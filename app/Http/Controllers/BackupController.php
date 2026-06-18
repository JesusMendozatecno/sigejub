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
    private function verifyAdmin(): void
    {
        abort_unless(auth()->user()?->rol === 'admin', 403, 'Solo administradores.');
    }

    public function index()
    {
        $this->verifyAdmin();
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
        $this->verifyAdmin();
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
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al generar respaldo: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function descargar($archivo)
    {
        $this->verifyAdmin();
        $path = storage_path("app/backups/{$archivo}");
        if (!file_exists($path)) {
            abort(404, 'Archivo de respaldo no encontrado.');
        }
        return response()->download($path, $archivo);
    }

    public function eliminar($archivo)
    {
        $this->verifyAdmin();
        $path = storage_path("app/backups/{$archivo}");
        if (!file_exists($path)) {
            return response()->json(['estado' => 'error', 'mensaje' => 'Archivo no encontrado.'], 404);
        }
        unlink($path);
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

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $bin = env('BACKUP_MYSQLDUMP_PATH') ?: 'mysqldump';
            if ($bin !== 'mysqldump') {
                $bin = realpath($bin) ?: 'mysqldump';
            }
            $cmd = "\"{$bin}\" --host={$host} --port={$port} --user={$user} --password={$pass} --routines --events --triggers --add-drop-table --databases {$db} 2>&1";
        } elseif ($driver === 'pgsql') {
            $bin = env('BACKUP_PGDUMP_PATH') ?: 'pg_dump';
            $cmd = "\"{$bin}\" --host={$host} --port={$port} --username={$user} --dbname={$db} --format=plain 2>&1";
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
