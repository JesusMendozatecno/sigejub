<?php
// Comando Artisan para reconstruir en disco los archivos almacenados en la
// base de datos (tabla `archivos_bd`).
//
// Se usa cuando se mueve/copia el sistema a otro equipo (o se restaura solo la
// base de datos): los datos viajan en la BD, pero las imágenes y documentos no.
// Este comando los vuelve a escribir en storage/app/public.
//
// Uso:
//   php artisan archivos:restaurar                     → restaura todo
//   php artisan archivos:restaurar --carpeta=expedientes/documentos
//   php artisan archivos:restaurar --forzar            → sobreescribe aunque coincida

namespace App\Console\Commands;

use App\Models\ArchivoBd;
use App\Services\ArchivoDbService;
use Illuminate\Console\Command;

class RestaurarArchivosBd extends Command
{
    protected $signature = 'archivos:restaurar
                            {--carpeta= : Restaurar solo una carpeta (ej. expedientes/documentos)}
                            {--forzar : Sobreescribe en disco aunque el archivo ya exista}';

    protected $description = 'Reconstruye en disco los archivos respaldados en la base de datos';

    public function handle(ArchivoDbService $servicio): int
    {
        $total = ArchivoBd::count();
        if ($total === 0) {
            $this->warn('No hay archivos respaldados en la base de datos.');
            return self::SUCCESS;
        }

        $this->info("Restaurando archivos desde la base de datos ({$total} registros)...");

        $restaurados = $servicio->restaurarDisco($this->option('carpeta') ?: null);

        $this->newLine();
        $this->info("✓ Listo. {$restaurados} archivo(s) restaurado(s) en disco.");

        return self::SUCCESS;
    }
}
