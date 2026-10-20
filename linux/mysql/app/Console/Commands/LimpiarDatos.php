<?php
// Comando Artisan para LIMPIAR los datos de prueba y dejar el sistema listo
// para llenados nuevos.
//
// Borra: trabajadores, solicitudes, expedientes, documentos, prestaciones,
// prestaciones sociales, nóminas, antecedentes de servicio, recibos de pago y
// sus documentos, además de los archivos asociados (disco + tabla archivos_bd).
//
// NO borra: usuarios, catálogos (cargos, áreas, grados, niveles, etc.),
// tasas de cambio ni configuración.
//
// ATENCIÓN: es DESTRUCTIVO e IRREVERSIBLE. Requiere confirmación explícita.
//
// Uso:
//   php artisan sigejub:limpiar-datos              → pide confirmación
//   php artisan sigejub:limpiar-datos --force      → sin preguntar
//   php artisan sigejub:limpiar-datos --dry-run    → solo muestra qué borraría

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Documento;
use App\Models\ReciboPago;
use App\Models\AntecedenteDocumento;
use App\Models\Prestacion;
use App\Models\Expediente;
use App\Services\ArchivoDbService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LimpiarDatos extends Command
{
    protected $signature = 'sigejub:limpiar-datos
                            {--force : No pedir confirmación}
                            {--dry-run : Solo mostrar cuántos registros se borrarían, sin borrar nada}
                            {--conservar-archivos : No borrar los archivos de disco/BD}';

    protected $description = 'Elimina trabajadores, solicitudes, expedientes y prestaciones para empezar llenados nuevos';

    /** Tablas a vaciar, en orden seguro (primero hijas, luego padres). */
    private const TABLAS = [
        'recibos_pago',
        'documentos',
        'antecedente_documentos',
        'antecedentes_servicio',
        'prestaciones',
        'nomina_trabajador',
        'nominas',
        'actividades_universitarias',
        'estudios',
        'expedientes',
        'solicitudes',
        'trabajadores',
    ];

    public function handle(ArchivoDbService $archivos): int
    {
        $dry = (bool) $this->option('dry-run');

        $this->newLine();
        $this->warn('  LIMPIEZA DE DATOS ' . ($dry ? '(SIMULACIÓN)' : ''));
        $this->line('  Se eliminarán trabajadores, solicitudes, expedientes y prestaciones.');
        $this->line('  Se conservan usuarios y catálogos.');
        $this->newLine();

        $conteos = [];
        foreach (self::TABLAS as $tabla) {
            $conteos[$tabla] = DB::table($tabla)->count();
        }
        $this->table(['Tabla', 'Registros'], array_map(
            fn($t, $c) => [$t, $c],
            array_keys($conteos),
            array_values($conteos)
        ));

        if ($dry) {
            $this->info('Simulación completada. No se borró nada. Ejecuta sin --dry-run para aplicar.');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('¿Confirma borrar TODOS estos datos? Esta acción es irreversible.', false)) {
            $this->warn('Cancelado. No se borró nada.');
            return self::SUCCESS;
        }

        // Recolecta rutas de archivos ANTES de borrar, para limpiar disco + BD.
        $rutas = [];
        if (!$this->option('conservar-archivos')) {
            $rutas = array_merge(
                Documento::whereNotNull('archivo')->pluck('archivo')->all(),
                ReciboPago::whereNotNull('archivo')->pluck('archivo')->all(),
                AntecedenteDocumento::whereNotNull('archivo')->pluck('archivo')->all(),
                Prestacion::whereNotNull('fideicomiso_documento')->pluck('fideicomiso_documento')->all(),
                Expediente::whereNotNull('foto_carnet')->pluck('foto_carnet')->all(),
                Expediente::whereNotNull('carta_aprobacion')->pluck('carta_aprobacion')->all(),
            );
            $rutas = array_values(array_unique(array_filter($rutas)));
        }

        $this->info('Borrando registros...');
        DB::transaction(function () {
            foreach (self::TABLAS as $tabla) {
                DB::table($tabla)->delete();
            }
        });

        if (!$this->option('conservar-archivos')) {
            $this->info('Eliminando archivos asociados...');
            foreach ($rutas as $ruta) {
                Storage::disk('public')->delete($ruta);
                $archivos->eliminar($ruta, 'public');
            }
        } else {
            $this->line('  (Se conservaron los archivos de disco y de la tabla archivos_bd.)');
        }

        $this->newLine();
        $this->info('✓ Datos limpiados. El sistema está listo para nuevos llenados.');

        return self::SUCCESS;
    }
}
