<?php
// Comando Artisan para respaldar en la base de datos (tabla `archivos_bd`) el
// contenido binario de todos los archivos que ya existen en disco.
//
// Se usa para migrar los archivos actuales (avatares, fotos, documentos, cartas,
// recibos, fideicomisos) que fueron creados antes de activar el respaldo en BD.
//
// Uso:
//   php artisan archivos:respaldar            → respalda todo lo referenciado en BD
//   php artisan archivos:respaldar --todo     → respalda TODO archivo presente en storage/app/public
//   php artisan archivos:respaldar --carpeta=expedientes/documentos

namespace App\Console\Commands;

use App\Models\ArchivoBd;
use App\Models\Expediente;
use App\Models\Documento;
use App\Models\ReciboPago;
use App\Models\AntecedenteDocumento;
use App\Models\Prestacion;
use App\Models\User;
use App\Services\ArchivoDbService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RespaldarArchivosBd extends Command
{
    protected $signature = 'archivos:respaldar
                            {--todo : Respaldar todos los archivos presentes en el disco público}
                            {--carpeta= : Respaldar solo una carpeta (ej. expedientes/documentos)}';

    protected $description = 'Respalda el contenido binario de los archivos en la base de datos (tabla archivos_bd)';

    public function handle(ArchivoDbService $servicio): int
    {
        $this->info('Respaldando archivos en la base de datos...');

        $total = 0;

        if ($this->option('todo') || $this->option('carpeta')) {
            $carpeta = $this->option('carpeta') ?: null;
            $total = $this->respaldarDiscoCompleto($servicio, $carpeta);
        } else {
            $total = $this->respaldarReferenciados($servicio);
        }

        $this->newLine();
        $this->info("✓ Listo. {$total} archivo(s) respaldado(s).");

        return self::SUCCESS;
    }

    /** Recorre todo storage/app/public (o una carpeta) y respalda cada archivo. */
    private function respaldarDiscoCompleto(ArchivoDbService $servicio, ?string $carpeta): int
    {
        $disco = Storage::disk('public');
        $archivos = $carpeta ? $disco->files(trim($carpeta, '/')) : $disco->allFiles();

        $barra = $this->output->createProgressBar(count($archivos));
        $barra->start();
        $total = 0;

        foreach ($archivos as $archivo) {
            if ($servicio->respaldar($archivo, 'public') !== null) {
                $total++;
            }
            $barra->advance();
        }

        $barra->finish();

        return $total;
    }

    /** Respaldar solo los archivos referenciados en columnas de la BD. */
    private function respaldarReferenciados(ArchivoDbService $servicio): int
    {
        $rutas = [];

        User::whereNotNull('avatar')->pluck('avatar')->each(fn($r) => $rutas[] = $r);
        Expediente::whereNotNull('foto_carnet')->pluck('foto_carnet')->each(fn($r) => $rutas[] = $r);
        Expediente::whereNotNull('carta_aprobacion')->pluck('carta_aprobacion')->each(fn($r) => $rutas[] = $r);
        Documento::whereNotNull('archivo')->pluck('archivo')->each(fn($r) => $rutas[] = $r);
        ReciboPago::whereNotNull('archivo')->pluck('archivo')->each(fn($r) => $rutas[] = $r);
        AntecedenteDocumento::whereNotNull('archivo')->pluck('archivo')->each(fn($r) => $rutas[] = $r);
        Prestacion::whereNotNull('fideicomiso_documento')->pluck('fideicomiso_documento')->each(fn($r) => $rutas[] = $r);

        $rutas = array_values(array_unique(array_filter($rutas)));

        if (empty($rutas)) {
            $this->warn('No se encontraron archivos referenciados en la base de datos.');
            return 0;
        }

        $barra = $this->output->createProgressBar(count($rutas));
        $barra->start();
        $total = 0;
        $faltantes = 0;

        foreach ($rutas as $ruta) {
            if ($servicio->respaldar($ruta, 'public') !== null) {
                $total++;
            } else {
                $faltantes++;
                $this->newLine();
                $this->warn("  No existe en disco (se omite): {$ruta}");
            }
            $barra->advance();
        }

        $barra->finish();

        if ($faltantes > 0) {
            $this->newLine();
            $this->warn("  {$faltantes} ruta(s) referenciadas no encontradas en disco.");
        }

        return $total;
    }
}
