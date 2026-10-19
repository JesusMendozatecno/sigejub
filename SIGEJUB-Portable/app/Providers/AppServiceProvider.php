<?php
// Proveedor de servicios principal de la aplicación.
// Registra los observers de los modelos para invalidar caché automáticamente.

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Solicitud;
use App\Models\Trabajador;
use App\Models\Expediente;
use App\Models\Prestacion;
use App\Observers\SolicitudObserver;
use App\Observers\TrabajadorObserver;
use App\Observers\ExpedienteObserver;
use App\Observers\PrestacionObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->asegurarStorageLink();

        Solicitud::observe(SolicitudObserver::class);
        Trabajador::observe(TrabajadorObserver::class);
        Expediente::observe(ExpedienteObserver::class);
        Prestacion::observe(PrestacionObserver::class);
    }

    /**
     * Garantiza que public/storage sea un enlace simbólico (o junction) a storage/app/public.
     * Si existe como directorio real (error común al copiar el proyecto), lo repara:
     * mueve su contenido al destino real y reemplaza por el enlace.
     */
    private function asegurarStorageLink(): void
    {
        $origen = storage_path('app/public');   // destino real
        $enlace = public_path('storage');       // lo que el navegador sirve

        if (!is_dir($origen)) {
            @mkdir($origen, 0755, true);
        }

        // No existe → crear enlace
        if (!file_exists($enlace)) {
            $this->crearEnlace($enlace, $origen);
            return;
        }

        // Ya es un enlace correcto → nada que hacer
        if (is_link($enlace) || (function_exists('is_junction') && is_junction($enlace))) {
            return;
        }

        // Existe como directorio real → mover contenido y reemplazar por enlace
        if (is_dir($enlace)) {
            $this->moverContenido($enlace, $origen);
            $this->eliminarDirReal($enlace);
            $this->crearEnlace($enlace, $origen);
        }
    }

    private function crearEnlace(string $enlace, string $destino): void
    {
        // Windows sin privilegios: junction funciona sin admin
        if (DIRECTORY_SEPARATOR === '\\') {
            @exec('mklink /J "' . $enlace . '" "' . $destino . '" 2>&1', $salida, $codigo);
            if ($codigo === 0 && (is_link($enlace) || is_dir($enlace))) {
                return;
            }
        }
        @symlink($destino, $enlace);
    }

    private function moverContenido(string $de, string $a): void
    {
        if (!is_dir($de)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($de, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $rutaDestino = $a . DIRECTORY_SEPARATOR . substr($item->getPathname(), strlen($de) + 1);
            if ($item->isDir()) {
                if (!is_dir($rutaDestino)) {
                    @mkdir($rutaDestino, 0755, true);
                }
            } else {
                $dirPadre = dirname($rutaDestino);
                if (!is_dir($dirPadre)) {
                    @mkdir($dirPadre, 0755, true);
                }
                // No pisar archivos ya existentes en el destino real
                if (!file_exists($rutaDestino)) {
                    @copy($item->getPathname(), $rutaDestino);
                }
            }
        }
    }

    private function eliminarDirReal(string $dir): void
    {
        if (!is_dir($dir) || is_link($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }
}
