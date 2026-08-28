<?php
// Controlador de respaldo del sistema.
// Genera copias de seguridad completas: base de datos (SQL) + archivos del proyecto (.zip).
// Solo accesible para usuarios con rol admin.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class BackupController extends Controller
{
    private function sanitizeFilename(string $filename): string
    {
        $filename = basename($filename);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            abort(400, 'Nombre de archivo invalido.');
        }
        return $filename;
    }

    public function index()
    {
        $backups = [];
        $disk = Storage::disk('local');
        if ($disk->exists('backups')) {
            $files = $disk->files('backups');
            rsort($files);
            foreach (array_slice($files, 0, 50) as $file) {
                $path = $disk->path($file);
                $backups[] = [
                    'nombre' => basename($file),
                    'tamano' => $this->formatBytes(filesize($path)),
                    'fecha' => date('Y-m-d H:i:s', filemtime($path)),
                    'ruta' => $file,
                ];
            }
        }
        return response()->json(['estado' => 'success', 'backups' => $backups]);
    }

    public function generar()
    {
        set_time_limit(300);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $dbFile = $backupDir . "/db_{$timestamp}.sql";
        $zipFinal = "sigejub_backup_{$timestamp}.zip";
        $zipPath = $backupDir . '/' . $zipFinal;

        try {
            $this->backupDatabase($dbFile);
            $this->backupFiles($zipPath, $dbFile);
            if (file_exists($dbFile)) {
                unlink($dbFile);
            }
            $tamano = filesize($zipPath);
            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Copia de seguridad generada exitosamente.',
                'archivo' => $zipFinal,
                'tamano' => $this->formatBytes($tamano),
            ]);
        } catch (\Exception $e) {
            if (file_exists($dbFile)) unlink($dbFile);
            \Illuminate\Support\Facades\Log::error('Error al generar respaldo: ' . $e->getMessage());
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al generar el respaldo. Consulte al administrador.',
            ], 500);
        }
    }

    public function descargar($archivo)
    {
        $archivo = $this->sanitizeFilename($archivo);
        $backupDir = storage_path('app/backups');
        $path = $backupDir . '/' . $archivo;
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $backupDir) !== 0 || !file_exists($realPath)) {
            abort(404, 'Archivo de respaldo no encontrado.');
        }
        return response()->download($realPath, $archivo);
    }

    public function eliminar($archivo)
    {
        $archivo = $this->sanitizeFilename($archivo);
        $backupDir = storage_path('app/backups');
        $path = $backupDir . '/' . $archivo;
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $backupDir) !== 0 || !file_exists($realPath)) {
            return response()->json(['estado' => 'error', 'mensaje' => 'Archivo no encontrado.'], 404);
        }
        unlink($realPath);
        return response()->json(['estado' => 'success', 'mensaje' => 'Respaldo eliminado.']);
    }

    private function backupDatabase(string $destino): void
    {
        $conn = config('database.default');
        $db = config("database.connections.{$conn}.database");
        $user = config("database.connections.{$conn}.username");
        $pass = config("database.connections.{$conn}.password");
        $host = config("database.connections.{$conn}.host");
        $port = config("database.connections.{$conn}.port");
        $driver = config("database.connections.{$conn}.driver");

        if ($driver === 'sqlite') {
            // Sin binario CLI para SQLite: usar el respaldo en PHP.
            $this->backupDatabasePhp($destino);
            return;
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $bin = env('BACKUP_MYSQLDUMP_PATH') ?: 'mysqldump';
            if ($bin !== 'mysqldump') {
                $bin = realpath($bin) ?: 'mysqldump';
            }
            $cmd = escapeshellarg($bin)
                . ' --host=' . escapeshellarg($host)
                . ' --port=' . escapeshellarg($port)
                . ' --user=' . escapeshellarg($user)
                . ' --password=' . escapeshellarg($pass)
                . ' --routines --events --triggers --add-drop-table'
                . ' --databases ' . escapeshellarg($db)
                . ' 2>&1';
        } elseif ($driver === 'pgsql') {
            $bin = env('BACKUP_PGDUMP_PATH') ?: 'pg_dump';
            $cmd = escapeshellarg($bin)
                . ' --host=' . escapeshellarg($host)
                . ' --port=' . escapeshellarg($port)
                . ' --username=' . escapeshellarg($user)
                . ' --dbname=' . escapeshellarg($db)
                . ' --format=plain 2>&1';
            putenv("PGPASSWORD={$pass}");
        } else {
            throw new \RuntimeException("Driver {$driver} no soportado para respaldo CLI.");
        }

        $output = [];
        $returnVar = 0;
        exec($cmd, $output, $returnVar);
        if ($returnVar !== 0) {
            throw new \RuntimeException("{$bin} falló: " . implode("\n", array_slice($output, 0, 20)));
        }
        file_put_contents($destino, implode("\n", $output));
        if (!file_exists($destino) || filesize($destino) < 10) {
            $this->backupDatabasePhp($destino);
        }
    }

    private function backupDatabasePhp(string $destino): void
    {
        $driver = config("database.connections." . config('database.default') . ".driver");

        if ($driver === 'pgsql') {
            $this->backupDatabasePhpPgsql($destino);
            return;
        }

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
    }

    private function backupDatabasePhpPgsql(string $destino): void
    {
        $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
        $sql = "-- SIGEJUB Database Backup (PHP Fallback PostgreSQL)\n-- Generated: " . now() . "\n";
        $sql .= "-- Nota: solo datos (INSERTs). La estructura se restaura con php artisan migrate.\n\n";
        foreach ($tables as $table) {
            if ($table->tablename === 'migrations') {
                continue;
            }
            $name = $table->tablename;
            $rows = DB::table($name)->get();
            if ($rows->count() === 0) {
                continue;
            }
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
    }

    private function backupFiles(string $zipPath, string $dbFile): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo ZIP.');
        }
        $zip->addFile($dbFile, 'database/backup.sql');

        $base = realpath(base_path());
        $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/backups'];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
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
            if ($file->isFile() && $file->isReadable()) {
                $zip->addFile($file->getRealPath(), $relative);
            }
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
