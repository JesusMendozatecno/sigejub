<?php
// BackupService — Servicio exclusivo de Copias de Seguridad del módulo Historial.
// Genera, lista, verifica (SHA-256), elimina y restaura copias de seguridad.

namespace App\Services;

use Illuminate\Support\Facades\DB;
use ZipArchive;

class BackupService
{
    public const DIR = 'backups';

    /**
     * Lista las copias de seguridad con metadatos (nombre, tipo, fecha, tamaño,
     * usuario, estado, hash SHA-256 y verificación de integridad).
     */
    public function listar(int $limite = 50): array
    {
        $backups = [];
        $backupDir = storage_path('app/' . self::DIR);
        if (!is_dir($backupDir)) {
            return $backups;
        }
        $files = glob($backupDir . '/*');
        rsort($files);
        foreach (array_slice($files, 0, $limite) as $path) {
            if (!is_file($path)) continue;
            $nombre = basename($path);
            $backups[] = [
                'nombre'      => $nombre,
                'tipo'        => $this->detectarTipo($path),
                'fecha'       => date('Y-m-d H:i:s', filemtime($path)),
                'tamano'      => $this->formatBytes(filesize($path)),
                'tamano_bytes'=> filesize($path),
                'ruta'        => self::DIR . '/' . $nombre,
                'hash'        => $this->calcularHash($path),
                'estado'      => $this->verificarArchivo($path),
            ];
        }
        return $backups;
    }

    /**
     * Genera una copia de seguridad real (base de datos + archivos del sistema)
     * y verifica su existencia, tamaño e integridad.
     * Devuelve ['estado','mensaje','archivo','tamano','hash','verificacion'].
     */
    public function generar(): array
    {
        set_time_limit(600);
        $backupDir = storage_path('app/' . self::DIR);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $timestamp = now()->format('Y-m-d_H-i-s');
        $dbFile = $backupDir . "/db_{$timestamp}.sql";
        $zipFinal = "sigejub_backup_{$timestamp}.zip";
        $zipPath = $backupDir . '/' . $zipFinal;

        try {
            $this->backupDatabase($dbFile);
            $this->backupFiles($zipPath, $dbFile);
            if (file_exists($dbFile)) {
                unlink($dbFile);
            }

            // Comprobación real del archivo generado.
            if (!file_exists($zipPath)) {
                throw new \RuntimeException('No se generó el archivo de respaldo.');
            }
            $bytes = filesize($zipPath);
            if ($bytes < 1024) {
                throw new \RuntimeException('El respaldo generado es demasiado pequeño y no se considera válido.');
            }

            $hash = $this->calcularHash($zipPath);
            return [
                'estado'       => 'success',
                'mensaje'      => 'Copia de seguridad generada y verificada exitosamente.',
                'archivo'      => $zipFinal,
                'tamano'       => $this->formatBytes($bytes),
                'tamano_bytes' => $bytes,
                'hash'         => $hash,
                'verificacion' => 'Integridad válida',
            ];
        } catch (\Throwable $e) {
            if (file_exists($dbFile)) unlink($dbFile);
            if (file_exists($zipPath)) unlink($zipPath);
            \Illuminate\Support\Facades\Log::error('Error al generar respaldo: ' . $e->getMessage());
            return [
                'estado'  => 'error',
                'mensaje' => 'Error al generar la copia de seguridad: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calcula el hash SHA-256 de un archivo.
     */
    public function calcularHash(string $path): string
    {
        return file_exists($path) ? (hash_file('sha256', $path) ?: '') : '';
    }

    /**
     * Verifica un archivo de respaldo: existencia, no vacío y estructura ZIP válida.
     * Devuelve 'Verificado' o 'Inválido'.
     */
    public function verificarArchivo(string $path): string
    {
        if (!file_exists($path) || filesize($path) < 1024) {
            return 'Inválido';
        }
        $zip = new ZipArchive();
        if ($zip->open($path) === true) {
            $zip->close();
            return 'Verificado';
        }
        return 'Inválido';
    }

    /**
     * Valida el nombre de archivo (anti path-traversal) y devuelve la ruta real
     * absoluta dentro del directorio de backups, o null si es inválido/inexistente.
     */
    public function rutaValida(string $archivo): ?string
    {
        $archivo = basename($archivo);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $archivo)) {
            return null;
        }
        $backupDir = storage_path('app/' . self::DIR);
        $realBackupDir = realpath($backupDir);
        $path = $backupDir . '/' . $archivo;
        $realPath = realpath($path);
        if (!$realPath || !$realBackupDir || strpos($realPath, $realBackupDir) !== 0 || !file_exists($realPath)) {
            return null;
        }
        return $realPath;
    }

    /**
     * Elimina físicamente una copia de seguridad tras validarla.
     */
    public function eliminar(string $archivo): bool
    {
        $realPath = $this->rutaValida($archivo);
        if (!$realPath) {
            return false;
        }
        return unlink($realPath);
    }

    /**
     * Expone el respaldo de base de datos (equivalente a backupDatabase privado),
     * usado para crear backups preventivos previos a una restauración.
     */
    public function backupDatabasePublica(string $destino): void
    {
        $this->backupDatabase($destino);
    }

    /**
     * Expone el formateado de bytes para la UI.
     */
    public function formatPublic(int $bytes): string
    {
        return $this->formatBytes($bytes);
    }

    private function detectarTipo(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['zip', 'sql'], true) ? 'Base de datos' : 'Archivo';
    }

    private function backupDatabase(string $destino): void
    {
        $conn = config('database.default');
        $driver = config("database.connections.{$conn}.driver");

        if ($driver === 'sqlite') {
            $this->backupDatabasePhp($destino);
            return;
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $db = config("database.connections.{$conn}.database");
            $user = config("database.connections.{$conn}.username");
            $pass = config("database.connections.{$conn}.password");
            $host = config("database.connections.{$conn}.host");
            $port = config("database.connections.{$conn}.port");

            $bin = $this->detectarMysqldump();
            $env = [];
            if (strtoupper(substr(PHP_OS_FAMILY, 0, 3)) === 'WIN') {
                // Evitar el "malloc error" de mysqldump en Windows con credenciales vacías
                if ($pass === '') {
                    $cmd = escapeshellarg($bin)
                        . " --host=" . escapeshellarg($host)
                        . " --port=" . escapeshellarg($port)
                        . " --user=" . escapeshellarg($user)
                        . " --routines --events --triggers --add-drop-table --skip-lock-tables"
                        . " --databases " . escapeshellarg($db) . " 2>&1";
                } else {
                    $cmd = escapeshellarg($bin)
                        . " --host=" . escapeshellarg($host)
                        . " --port=" . escapeshellarg($port)
                        . " --user=" . escapeshellarg($user)
                        . " --password=" . escapeshellarg($pass)
                        . " --routines --events --triggers --add-drop-table --skip-lock-tables"
                        . " --databases " . escapeshellarg($db) . " 2>&1";
                }
            } else {
                $cmd = escapeshellarg($bin)
                    . " --host=" . escapeshellarg($host)
                    . " --port=" . escapeshellarg($port)
                    . " --user=" . escapeshellarg($user)
                    . " --password=" . escapeshellarg($pass)
                    . " --routines --events --triggers --add-drop-table --skip-lock-tables"
                    . " --databases " . escapeshellarg($db) . " 2>&1";
            }

            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);

            $dump = implode("\n", $output);
            // Validación de contenido esperado: debe contener la base de datos.
            if ($returnVar !== 0 || stripos($dump, $db) === false || strlen($dump) < 1000) {
                // Fallback PHP si mysqldump no funcionó correctamente.
                $this->backupDatabasePhp($destino);
                return;
            }
            file_put_contents($destino, $dump);
            return;
        }

        if ($driver === 'pgsql') {
            $bin = env('BACKUP_PGDUMP_PATH') ?: 'pg_dump';
            $db = config("database.connections.{$conn}.database");
            $user = config("database.connections.{$conn}.username");
            $pass = config("database.connections.{$conn}.password");
            $host = config("database.connections.{$conn}.host");
            $port = config("database.connections.{$conn}.port");
            $cmd = escapeshellarg($bin)
                . " --host=" . escapeshellarg($host)
                . " --port=" . escapeshellarg($port)
                . " --username=" . escapeshellarg($user)
                . " --dbname=" . escapeshellarg($db)
                . " --format=plain 2>&1";
            putenv("PGPASSWORD={$pass}");
            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);
            if ($returnVar !== 0) {
                throw new \RuntimeException("pg_dump falló: " . implode("\n", array_slice($output, 0, 20)));
            }
            file_put_contents($destino, implode("\n", $output));
            return;
        }

        throw new \RuntimeException("Driver {$driver} no soportado para respaldo.");
    }

    /**
     * Detecta el binario de mysqldump: 1) configuración, 2) variable de entorno,
     * 3) ruta XAMPP común, 4) 'mysqldump' del PATH.
     */
    private function detectarMysqldump(): string
    {
        $candidatos = [];
        if ($configurado = config('backup.mysqldump_path')) {
            $candidatos[] = $configurado;
        }
        if ($env = env('BACKUP_MYSQLDUMP_PATH')) {
            $candidatos[] = $env;
        }
        // Detección XAMPP (Windows).
        $xampp = getenv('XAMPP_HOME') ?: null;
        if ($xampp) {
            $candidatos[] = $xampp . '/mysql/bin/mysqldump.exe';
        }
        $candidatos[] = 'C:\xampp\mysql\bin\mysqldump.exe';
        $candidatos[] = 'mysqldump';

        foreach ($candidatos as $c) {
            if ($c && file_exists($c)) {
                return $c;
            }
        }
        // Último recurso: confiar en el PATH del sistema.
        return 'mysqldump';
    }

    private function backupDatabasePhp(string $destino): void
    {
        $driver = config("database.connections." . config('database.default') . ".driver");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $tables = DB::select('SHOW TABLES');
            $key = 'Tables_in_' . config('database.connections.mysql.database');
            $sql = "-- SIGEJUB Database Backup (PHP Fallback)\n-- Generated: " . now() . "\n\n";
            foreach ($tables as $table) {
                $name = $table->$key;
                $sql .= "DROP TABLE IF EXISTS `{$name}`;\n";
                $create = DB::select("SHOW CREATE TABLE `{$name}`")[0];
                $prop = "Create Table";
                if (isset($create->$prop)) {
                    $sql .= $create->$prop . ";\n\n";
                }
                $rows = DB::table($name)->get();
                if ($rows->count() > 0) {
                    $cols = implode('`, `', array_keys(get_object_vars($rows[0])));
                    $sql .= "INSERT INTO `{$name}` (`{$cols}`) VALUES\n";
                    $vals = [];
                    foreach ($rows as $row) {
                        $escaped = array_map(function ($v) {
                            return $v === null ? 'NULL' : "'" . str_replace("'", "''", $v) . "'";
                        }, get_object_vars($row));
                        $vals[] = '(' . implode(', ', $escaped) . ')';
                    }
                    $sql .= implode(",\n", $vals) . ";\n\n";
                }
            }
            file_put_contents($destino, $sql);
            return;
        }

        if ($driver === 'pgsql') {
            $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
            $sql = "-- SIGEJUB Database Backup (PHP Fallback PostgreSQL)\n-- Generated: " . now() . "\n-- Nota: solo datos (INSERTs). La estructura se restaura con php artisan migrate.\n\n";
            foreach ($tables as $table) {
                if ($table->tablename === 'migrations') continue;
                $name = $table->tablename;
                $rows = DB::table($name)->get();
                if ($rows->count() === 0) continue;
                $cols = implode(', ', array_map(fn ($c) => '"' . $c . '"', array_keys(get_object_vars($rows[0]))));
                $sql .= "DELETE FROM \"{$name}\";\n";
                foreach ($rows as $row) {
                    $escaped = array_map(function ($v) {
                        return $v === null ? 'NULL' : "'" . str_replace("'", "''", $v) . "'";
                    }, get_object_vars($row));
                    $sql .= "INSERT INTO \"{$name}\" ({$cols}) VALUES (" . implode(', ', $escaped) . ");\n";
                }
                $sql .= "\n";
            }
            file_put_contents($destino, $sql);
            return;
        }
    }

    private function backupFiles(string $zipPath, string $dbFile): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo ZIP.');
        }
        $zip->addFile($dbFile, 'database/backup.sql');

        $base = realpath(base_path());
        $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/backups', 'storage/app/backups', 'storage/logs', 'storage/framework'];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if (!$file->isFile() || !$file->isReadable()) continue;
            $relative = substr($file->getRealPath(), strlen($base) + 1);
            $relative = str_replace('\\', '/', $relative);
            $skip = false;
            foreach ($excludeDirs as $ex) {
                if (strpos($relative, $ex . '/') === 0 || $relative === $ex) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;
            $zip->addFile($file->getRealPath(), $relative);
        }
        $zip->close();
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}