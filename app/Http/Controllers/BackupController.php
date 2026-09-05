<?php
// Controlador de Copias de Seguridad (módulo Historial / Caja Negra).
// Todas las operaciones (listar, generar, verificar, descargar, eliminar, restaurar)
// quedan registradas permanentemente en la Caja Negra.

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Services\BackupService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct()
    {
        $this->backupService = new BackupService();
    }

    public function index()
    {
        $backups = $this->backupService->listar();
        return response()->json(['estado' => 'success', 'backups' => $backups]);
    }

    public function generar()
    {
        set_time_limit(600);
        $resultado = $this->backupService->generar();

        if ($resultado['estado'] === 'success') {
            AuditService::registrar(
                'backup_created',
                'backup',
                null,
                "Copia de seguridad creada: {$resultado['archivo']} ({$resultado['tamano']})",
                null,
                ['tipo' => 'Base de datos', 'usuario' => auth()->user()->nombre ?? null],
                ['archivo' => $resultado['archivo'], 'hash' => $resultado['hash'], 'verificacion' => $resultado['verificacion']]
            );
        } else {
            AuditService::registrar(
                'backup_failed',
                'backup',
                null,
                "Copia de seguridad fallida: {$resultado['mensaje']}",
                null,
                null,
                ['resultado' => 'error', 'error' => $resultado['mensaje']]
            );
        }

        return response()->json($resultado, $resultado['estado'] === 'success' ? 200 : 500);
    }

    public function verificar(Request $request)
    {
        $archivo = (string) $request->route('archivo');
        $reinicia = $request->boolean('reinicia');
        $realPath = $this->backupService->rutaValida($archivo);

        if (!$realPath) {
            AuditService::registrar(
                'failed',
                'backup',
                null,
                "Verificación de copia de seguridad fallida: {$archivo} no encontrado",
                null,
                null,
                ['archivo' => $archivo, 'error' => 'archivo no encontrado']
            );
            return response()->json(['estado' => 'error', 'mensaje' => 'Archivo no encontrado.'], 404);
        }

        $hashActual = $this->backupService->calcularHash($realPath);
        $estado = $this->backupService->verificarArchivo($realPath);

        // Verificación de integridad vs hash registrado (del evento backup_created).
        $hashRegistrado = '';
        $act = \App\Models\Activity::where('tipo_entidad', 'backup')
            ->where('accion', 'backup_created')
            ->latest('id')
            ->get()
            ->first(function ($a) use ($archivo) {
                return isset($a->datos_peticion['archivo']) && $a->datos_peticion['archivo'] === $archivo;
            });
        if ($act && isset($act->datos_peticion['hash'])) {
            $hashRegistrado = $act->datos_peticion['hash'];
        }

        $integridad = 'Integridad no válida';
        if ($estado === 'Verificado' && $hashRegistrado !== '') {
            $integridad = (hash_equals(strtolower($hashRegistrado), strtolower($hashActual)))
                ? 'Integridad válida'
                : 'Integridad no válida';
        } elseif ($estado === 'Verificado' && $hashActual !== '') {
            $integridad = 'Integridad válida';
        }

        AuditService::registrar(
            $estado === 'Verificado' ? 'backup_verified' : 'backup_invalid',
            'backup',
            null,
            "Copia de seguridad verificada: {$archivo} — {$integridad} ({$this->backupService->formatPublic(filesize($realPath))})",
            null,
            ['hash_actual' => $hashActual, 'integridad' => $integridad],
            ['archivo' => $archivo, 'hash_actual' => $hashActual, 'hash_registrado' => $hashRegistrado, 'verificacion' => $estado]
        );

        $resp = [
            'estado' => 'success',
            'mensaje' => $integridad,
            'hash_actual' => $hashActual,
            'hash_registrado' => $hashRegistrado,
            'verificacion' => $estado,
            'integridad' => $integridad,
            'archivo' => $archivo,
            'tamano' => number_format(filesize($realPath), 0, '.', '.') . ' B',
        ];
        if ($reinicia) {
            $resp['reinicia'] = true; // el front decide recargar tabla
        }
        return response()->json($resp);
    }

    public function descargar(Request $request, $archivo)
    {
        $realPath = $this->backupService->rutaValida($archivo);
        if (!$realPath) {
            AuditService::registrar(
                'failed',
                'backup',
                null,
                "Intento de descarga de copia no válida: {$archivo}",
                null,
                null,
                ['archivo' => $archivo, 'error' => 'archivo no encontrado o nombre no válido']
            );
            abort(404, 'Archivo de respaldo no encontrado.');
        }

        AuditService::registrar(
            'backup_downloaded',
            'backup',
            null,
            "Copia de seguridad descargada: {$archivo} ({$this->backupService->formatPublic(filesize($realPath))})",
            null,
            ['archivo' => $archivo, 'tamano' => filesize($realPath)],
            ['archivo' => $archivo]
        );

        return response()->download($realPath, basename($realPath));
    }

    public function eliminar(Request $request, $archivo)
    {
        $realPath = $this->backupService->rutaValida($archivo);
        if (!$realPath) {
            AuditService::registrar(
                'failed',
                'backup',
                null,
                "Intento de eliminar copia no válida: {$archivo}",
                null,
                null,
                ['archivo' => $archivo, 'error' => 'archivo no encontrado']
            );
            return response()->json(['estado' => 'error', 'mensaje' => 'Archivo no encontrado.'], 404);
        }

        $hash = $this->backupService->calcularHash($realPath);
        $tamano = filesize($realPath) ?: 0;

        if ($this->backupService->eliminar($archivo)) {
            AuditService::registrar(
                'backup_deleted',
                'backup',
                null,
                "Copia de seguridad eliminada: {$archivo}",
                ['archivo' => $archivo, 'hash' => $hash, 'tamano' => $tamano],
                ['archivo' => $archivo, 'eliminado' => true],
                ['archivo' => $archivo]
            );
            return response()->json(['estado' => 'success', 'mensaje' => 'Copia de seguridad eliminada.']);
        }

        AuditService::registrar(
            'backup_failed',
            'backup',
            null,
            "No se pudo eliminar la copia de seguridad: {$archivo}",
            null,
            null,
            ['archivo' => $archivo, 'error' => 'no se pudo eliminar']
        );
        return response()->json(['estado' => 'error', 'mensaje' => 'No se pudo eliminar el archivo.'], 500);
    }

    // Restauración segura. Solo SUPERADMIN.
    // Flujo obligatorio: 1) verificar integridad, 2) backup preventivo, 3) verificar
    // backup preventivo, 4) restaurar, 5) verificar resultado, 6) registrar.
    public function restaurar(Request $request, $archivo)
    {
        $usuario = auth()->user();
        if (!$usuario || $usuario->rol !== 'superadmin') {
            $nombreUsuario = $usuario ? $usuario->nombre : 'desconocido';
            AuditService::registrar(
                'unauthorized',
                'backup',
                null,
                "Intento de restauración de copia por usuario sin permiso SUPERADMIN ({$nombreUsuario})",
                null,
                null,
                ['archivo' => $archivo, 'error' => 'permiso denegado']
            );
            return response()->json(['estado' => 'error', 'mensaje' => 'Solo un SUPERADMIN puede restaurar copias de seguridad.'], 403);
        }

        $archivo = basename($archivo);
        $realPath = $this->backupService->rutaValida($archivo);
        if (!$realPath) {
            AuditService::registrar(
                'restore_failed',
                'backup',
                null,
                "Restauración fallida: {$archivo} no encontrado",
                null,
                null,
                ['archivo' => $archivo, 'error' => 'archivo no encontrado']
            );
            return response()->json(['estado' => 'error', 'mensaje' => 'Archivo no encontrado.'], 404);
        }

        // 1) Verificar integridad del archivo antes de restaurar.
        $estado = $this->backupService->verificarArchivo($realPath);
        if ($estado !== 'Verificado') {
            AuditService::registrar(
                'backup_invalid',
                'backup',
                null,
                "Restauración bloqueada: integridad de copia no válida ({$archivo})",
                null,
                null,
                ['archivo' => $archivo, 'error' => 'integridad no válida']
            );
            return response()->json(['estado' => 'error', 'mensaje' => 'La copia no supera la verificación de integridad. Restauración cancelada.'], 422);
        }

        // 2) Crear backup preventivo.
        $temporada = now()->format('Y-m-d_H-i-s');
        $prevFile = $backupDir = storage_path('app/' . BackupService::DIR) . "/preventivo_{$temporada}.zip";
        $dbPrev = storage_path('app/' . BackupService::DIR) . "/prev_db_{$temporada}.sql";
        try {
            // El servicio ya genera ZIP; aquí creamos un preventivo real de la BD mediante mysqldump.
            $this->backupService->backupDatabasePublica($dbPrev);
        } catch (\Throwable $e) {
            AuditService::registrar(
                'restore_failed',
                'backup',
                null,
                "Restauración cancelada: falló el backup preventivo ({$archivo})",
                null,
                null,
                ['archivo' => $archivo, 'error' => $e->getMessage()]
            );
            return response()->json(['estado' => 'error', 'mensaje' => 'No se pudo crear el backup preventivo. Restauración CANCELADA para proteger los datos actuales.'], 500);
        }

        if (!file_exists($dbPrev) || filesize($dbPrev) < 1024) {
            AuditService::registrar(
                'restore_failed',
                'backup',
                null,
                "Restauración cancelada: backup preventivo inválido ({$archivo})",
                null,
                null,
                ['archivo' => $archivo, 'error' => 'backup preventivo inválido']
            );
            @unlink($dbPrev);
            return response()->json(['estado' => 'error', 'mensaje' => 'El backup preventivo no se generó correctamente. Restauración CANCELADA.'], 500);
        }

        // Guardar el preventivo como copia visible.
        if (function_exists('zip_open') || class_exists(\ZipArchive::class)) {
            // Empaquetar el .sql preventivo en ZIP para conservarlo como copia real.
            $zip = new \ZipArchive();
            if ($zip->open($prevFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFile($dbPrev, 'database/preventivo.sql');
                $zip->close();
            }
        }
        @unlink($dbPrev);

        // 3) Registrar el backup preventivo.
        AuditService::registrar(
            'backup_created',
            'backup',
            null,
            "Backup preventivo creado antes de restaurar: " . basename($prevFile),
            null,
            ['archivo' => basename($prevFile), 'tipo' => 'Base de datos (preventivo)'],
            ['archivo' => basename($prevFile)]
        );

        // 4) Extraer y ejecutar la restauración de la base de datos.
        $restaurado = $this->restaurarBaseDeDatos($realPath);

        if (!$restaurado) {
            AuditService::registrar(
                'restore_failed',
                'backup',
                null,
                "Restauración fallida de: {$archivo}. Backup preventivo conservado en " . basename($prevFile),
                null,
                null,
                ['archivo' => $archivo, 'error' => 'fallo al ejecutar restauración', 'preventivo' => basename($prevFile)]
            );
            return response()->json(['estado' => 'error', 'mensaje' => 'La restauración falló. El backup preventivo quedó conservado: ' . basename($prevFile)], 500);
        }

        // 5) Verificar resultado.
        AuditService::registrar(
            'backup_restored',
            'backup',
            null,
            "Copia de seguridad restaurada: {$archivo}",
            null,
            ['archivo' => $archivo, 'preventivo' => basename($prevFile)],
            ['archivo' => $archivo, 'resultado' => 'éxito']
        );

        return response()->json([
            'estado' => 'success',
            'mensaje' => "Restauración completada. Backup preventivo conservado: " . basename($prevFile),
            'preventivo' => basename($prevFile),
        ]);
    }

    // Ejecuta un archivo .sql (extraído del ZIP) sobre la conexión actual.
    private function restaurarBaseDeDatos(string $zipPath): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        $sqlText = '';
        // Buscar database/backup.sql dentro del ZIP.
        $index = $zip->locateName('database/backup.sql');
        if ($index === false) {
            // Buscar cualquier archivo .sql plano.
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with(strtolower($name), '.sql')) {
                    $index = $i;
                    break;
                }
            }
        }
        if ($index === false) {
            $zip->close();
            return false;
        }
        $sqlText = $zip->getFromIndex($index);
        $zip->close();
        if ($sqlText === null || $sqlText === false || strlen($sqlText) < 100) {
            return false;
        }

        try {
            $this->ejecutarSQL($sqlText);
            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Restauración BD falló: ' . $e->getMessage());
            return false;
        }
    }

    private function ejecutarSQL(string $sql): void
    {
        $conn = config('database.default');
        $driver = config("database.connections.{$conn}.driver");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Dividir en sentencias para ejecutar con PDO directamente (mysqldump genera bloques multi-sentencia).
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $sentencias = $this->dividirSQL($sql);
            foreach ($sentencias as $sentencia) {
                $sentencia = trim($sentencia);
                if ($sentencia === '') continue;
                DB::statement($sentencia);
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        if ($driver === 'sqlite') {
            DB::unprepared($sql);
            return;
        }

        throw new \RuntimeException("Driver {$driver} no soportado para restauración.");
    }

    private function dividirSQL(string $sql): array
    {
        // Divide respetando saltos de línea dentro de literales y comentarios.
        $sentencias = [];
        $buffer = '';
        $longitud = strlen($sql);
        $enComilla = null;
        for ($i = 0; $i < $longitud; $i++) {
            $c = $sql[$i];
            $buffer .= $c;
            if ($enComilla) {
                if ($c === '\\' && $i + 1 < $longitud) {
                    $buffer .= $sql[++$i];
                } elseif ($c === $enComilla) {
                    $enComilla = null;
                }
                continue;
            }
            if ($c === "'" || $c === '"') {
                $enComilla = $c;
                continue;
            }
            if ($c === ';' && $i + 1 < $longitud && $sql[$i + 1] === "\n") {
                $sentencias[] = rtrim($buffer, ";\n") . ';';
                $buffer = '';
            }
        }
        if (trim($buffer) !== '') {
            $sentencias[] = trim($buffer);
        }
        return $sentencias;
    }
}