<?php
// Servicio que mantiene una COPIA BINARIA de los archivos del sistema dentro de
// la base de datos (tabla `archivos_bd`).
//
// Estrategia híbrida:
//   - El archivo se sigue guardando en disco (storage/app/public) para servirlo
//     rápido por /storage/...
//   - Además se guarda el mismo contenido (base64) en la BD como respaldo.
//   - Al mover/copiar el sistema a otro equipo, `php artisan archivos:restaurar`
//     reconstruye el disco a partir de la BD.
//
// Todos los puntos que suben/borran archivos deben llamar a `respaldar()`,
// `respaldarDisco()` o `eliminar()`.

namespace App\Services;

use App\Models\ArchivoBd;
use Illuminate\Support\Facades\Storage;

class ArchivoDbService
{
    public const DISCO = 'public';

    /**
     * Guarda (o actualiza) en la BD el contenido binario de un archivo que ya
     * existe en disco.
     *
     * @param string      $ruta            Ruta relativa en el disco (ej. expedientes/fotos/exp_12.jpg).
     * @param string      $disco           Disco de Laravel.
     * @param string|null $nombreOriginal  Nombre original del archivo subido.
     * @return ArchivoBd|null
     */
    public function respaldar(string $ruta, string $disco = self::DISCO, ?string $nombreOriginal = null): ?ArchivoBd
    {
        $ruta = trim($ruta, '/');
        if ($ruta === '') {
            return null;
        }

        $storage = Storage::disk($disco);
        if (!$storage->exists($ruta)) {
            return null;
        }

        $contenido = $storage->get($ruta);
        if ($contenido === null) {
            return null;
        }

        $mime = null;
        try {
            $mime = $storage->mimeType($ruta) ?: null;
        } catch (\Throwable $e) {
            $mime = null;
        }

        $archivo = ArchivoBd::firstOrCreate(
            ['disco' => $disco, 'ruta' => $ruta],
            [
                'nombre_original' => $nombreOriginal,
                'mime' => $mime,
                'tamano' => strlen($contenido),
                'hash' => hash('sha256', $contenido),
                'contenido' => null,
            ]
        );

        // Trocea el base64 en bloques para no exceder max_allowed_packet.
        $archivo->guardarBinario($contenido, $mime, $nombreOriginal);

        return $archivo;
    }

    /**
     * Guarda en la BD el contenido binario recibido directamente (sin pasar por
     * disco). Útil si en el futuro se sube algo que no toque el disco.
     */
    public function respaldarBinario(string $ruta, string $contenido, string $disco = self::DISCO, ?string $mime = null, ?string $nombreOriginal = null): ArchivoBd
    {
        $archivo = ArchivoBd::firstOrCreate(
            ['disco' => $disco, 'ruta' => trim($ruta, '/')],
            [
                'nombre_original' => $nombreOriginal,
                'mime' => $mime,
                'tamano' => strlen($contenido),
                'hash' => hash('sha256', $contenido),
                'contenido' => null,
            ]
        );

        $archivo->guardarBinario($contenido, $mime, $nombreOriginal);

        return $archivo;
    }

    /**
     * Respalda todos los archivos que empiecen con "{base}." dentro de la
     * carpeta indicada (sirve tras putFileAs para capturar la extensión real).
     */
    public function respaldarPorBase(string $carpeta, string $base, string $disco = self::DISCO): void
    {
        $carpeta = trim($carpeta, '/');
        $storage = Storage::disk($disco);

        foreach ($storage->files($carpeta) as $archivo) {
            if (str_starts_with(basename($archivo), $base . '.')) {
                $this->respaldar($archivo, $disco);
            }
        }
    }

    /** Elimina de la BD el respaldo de una ruta (y sus variantes por base). */
    public function eliminar(string $ruta, string $disco = self::DISCO): void
    {
        $ruta = trim($ruta, '/');
        if ($ruta === '') {
            return;
        }

        ArchivoBd::where('disco', $disco)->where('ruta', $ruta)->delete();
    }

    /** Elimina de la BD los respaldos cuyo nombre empiece con "{base}.". */
    public function eliminarPorBase(string $carpeta, string $base, string $disco = self::DISCO): void
    {
        $carpeta = trim($carpeta, '/');
        $prefijo = ($carpeta !== '' ? $carpeta . '/' : '') . $base . '.';

        ArchivoBd::where('disco', $disco)
            ->where('ruta', 'like', $prefijo . '%')
            ->delete();
    }

    /**
     * Restaura en disco todos los archivos respaldados en la BD (o solo los de
     * una carpeta si se indica). Devuelve el número de archivos restaurados.
     */
    public function restaurarDisco(?string $carpeta = null, string $disco = self::DISCO): int
    {
        $query = ArchivoBd::where('disco', $disco);
        if ($carpeta) {
            $query->where('ruta', 'like', trim($carpeta, '/') . '/%');
        }

        $storage = Storage::disk($disco);
        $total = 0;

        $query->with('bloques')->orderBy('id')->chunkById(50, function ($registros) use ($storage, &$total) {
            foreach ($registros as $registro) {
                $binario = $registro->binario();
                if ($binario === null) {
                    continue;
                }

                // No sobreescribir si ya existe y coincide el hash.
                if ($storage->exists($registro->ruta)) {
                    $actual = $storage->get($registro->ruta);
                    if ($actual !== null && hash('sha256', $actual) === $registro->hash) {
                        continue;
                    }
                }

                $storage->put($registro->ruta, $binario);
                $total++;
            }
        });

        return $total;
    }
}
