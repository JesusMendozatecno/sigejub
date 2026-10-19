<?php
// Servicio de almacenamiento de imágenes (avatares de usuario y fotos de expediente).
//
// Objetivo: que cada registro tenga SIEMPRE una sola imagen y con un nombre
// determinista (predecible) basado en su ID, de modo que:
//   - Al subir una imagen nueva se SOBRESCRIBE la anterior (mismo nombre).
//   - Si el usuario cambia de extensión (.jpg -> .png), se borra la anterior.
//   - No se acumulan archivos huérfanos con nombres aleatorios.
//
// Convenciones de nombres:
//   - Avatar de usuario:   avatars/u_{id}.{ext}
//   - Foto de expediente:  expedientes/fotos/exp_{id}.{ext}
//   - Avatar por defecto:  public/img/avatar-default.png  ->  avatars/u_{id}.png

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageStorageService
{
    /** Disco público de Laravel (storage/app/public enlazado a public/storage). */
    public const DISCO = 'public';

    // Ruta de la imagen por defecto (dentro de public/) que se copia a cada usuario nuevo.
    public const AVATAR_POR_DEFECTO = 'img/avatar-default.png';

    /**
     * Guarda la imagen con el nombre determinista indicado y elimina las
     * versiones anteriores (otra extensión u otro nombre aleatorio antiguo).
     *
     * @param  UploadedFile  $archivo       Archivo subido por el formulario.
     * @param  string        $carpeta       Carpeta destino dentro del disco (ej: avatars).
     * @param  string        $base          Nombre base SIN extensión (ej: u_5, exp_12).
     * @param  string|null   $rutaAnterior  Ruta guardada actualmente en la BD (para limpiarla).
     * @return string                       Ruta relativa guardada (ej. avatars/u_5.jpg).
     */
    public function guardar(UploadedFile $archivo, string $carpeta, string $base, ?string $rutaAnterior = null): string
    {
        $carpeta = trim($carpeta, '/');
        $extension = strtolower($archivo->getClientOriginalExtension() ?: $archivo->guessExtension() ?: 'jpg');
        $nombre = $base . '.' . $extension;
        $destino = $carpeta . '/' . $nombre;

        // putFileAs guarda con el nombre exacto indicado (sobrescribe si ya existe).
        Storage::disk(self::DISCO)->putFileAs($carpeta, $archivo, $nombre);

        $this->limpiarVariantes($carpeta, $base, $destino, $rutaAnterior);

        return $destino;
    }

    /**
     * Copia la imagen de perfil por defecto para un usuario nuevo.
     *
     * @param  string $carpeta        Carpeta destino (ej. avatars).
     * @param  string $base           Nombre base sin extensión (ej. u_12).
     * @param  string $imagenPorDefecto Ruta absoluta de la imagen estática de origen.
     * @return string|null            Ruta relativa guardada, o null si no existe el origen.
     */
    public function copiarPorDefecto(string $carpeta, string $base, string $imagenPorDefecto): ?string
    {
        if (!is_file($imagenPorDefecto)) {
            return null;
        }

        $carpeta = trim($carpeta, '/');
        $extension = strtolower(pathinfo($imagenPorDefecto, PATHINFO_EXTENSION)) ?: 'png';
        $nombre = $base . '.' . $extension;
        $destino = $carpeta . '/' . $nombre;

        Storage::disk(self::DISCO)->put($destino, file_get_contents($imagenPorDefecto));

        $this->limpiarVariantes($carpeta, $base, $destino, null);

        return $destino;
    }

    /**
     * Elimina todas las imágenes de un registro (cualquier extensión).
     *
     * @param string      $carpeta Carpeta donde viven las imágenes.
     * @param string|null $base    Nombre base sin extensión (si se conoce).
     * @param string|null $rutaActual Ruta registrada en la base de datos (respaldo).
     */
    public function eliminar(string $carpeta, ?string $base = null, ?string $rutaActual = null): void
    {
        $carpeta = trim($carpeta, '/');

        if ($base) {
            foreach ($this->variantesEnDisco($carpeta, $base) as $archivo) {
                Storage::disk(self::DISCO)->delete($archivo);
            }
        }

        if ($rutaActual) {
            Storage::disk(self::DISCO)->delete($rutaActual);
        }
    }

    /**
     * Borra los archivos de la carpeta que empiecen con "{base}." salvo el que
     * acabamos de guardar, y además la ruta antigua si tenía otro nombre.
     */
    private function limpiarVariantes(string $carpeta, string $base, string $destino, ?string $rutaAnterior): void
    {
        foreach ($this->variantesEnDisco($carpeta, $base) as $archivo) {
            if ($archivo !== $destino) {
                Storage::disk(self::DISCO)->delete($archivo);
            }
        }

        if ($rutaAnterior && $rutaAnterior !== $destino) {
            Storage::disk(self::DISCO)->delete($rutaAnterior);
        }
    }

    private function variantesEnDisco(string $carpeta, string $base): array
    {
        $encontrados = [];

        foreach (Storage::disk(self::DISCO)->files($carpeta) as $archivo) {
            if (str_starts_with(basename($archivo), $base . '.')) {
                $encontrados[] = $archivo;
            }
        }

        return $encontrados;
    }
}
