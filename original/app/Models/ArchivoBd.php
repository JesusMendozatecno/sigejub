<?php
// Modelo que representa la copia binaria de un archivo del sistema almacenada
// en la base de datos (tabla `archivos_bd`). El contenido se guarda en base64.
//
// Para no superar `max_allowed_packet` del servidor (1 MB por defecto), el
// contenido se divide en bloques (`archivos_bd_bloques`). La columna
// `contenido` se conserva para leer respaldos creados por versiones anteriores.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoBd extends Model
{
    protected $table = 'archivos_bd';

    /** Tamaño de cada bloque en bytes de base64 (margen seguro bajo 1 MB). */
    public const BLOQUE_TAMANO = 240000;

    protected $fillable = [
        'ruta',
        'disco',
        'nombre_original',
        'mime',
        'tamano',
        'hash',
        'contenido',
    ];

    protected $casts = [
        'tamano' => 'integer',
    ];

    public function bloques()
    {
        return $this->hasMany(ArchivoBdBloque::class, 'archivo_bd_id')->orderBy('orden');
    }

    /**
     * Guarda el binario recibido en base64, troceándolo en bloques. Actualiza los
     * metadatos (mime, tamaño, hash) y limpia el contenido monolítico antiguo.
     */
    public function guardarBinario(string $binario, ?string $mime = null, ?string $nombreOriginal = null): void
    {
        $base64 = base64_encode($binario);

        $this->update([
            'nombre_original' => $nombreOriginal ?? $this->nombre_original,
            'mime' => $mime ?? $this->mime,
            'tamano' => strlen($binario),
            'hash' => hash('sha256', $binario),
            'contenido' => null,
        ]);

        $this->bloques()->delete();

        $orden = 0;
        foreach (str_split($base64, self::BLOQUE_TAMANO) as $parte) {
            ArchivoBdBloque::create([
                'archivo_bd_id' => $this->id,
                'orden' => $orden++,
                'datos' => $parte,
            ]);
        }
    }

    /** Devuelve el binario decodificado (desde bloques o desde `contenido`). */
    public function binario(): ?string
    {
        $base64 = null;

        if ($this->contenido !== null && $this->contenido !== '') {
            $base64 = $this->contenido;
        } else {
            $partes = $this->bloques()->pluck('datos')->all();
            if (!empty($partes)) {
                $base64 = implode('', $partes);
            }
        }

        if ($base64 === null || $base64 === '') {
            return null;
        }

        $data = base64_decode($base64, true);

        return $data === false ? null : $data;
    }
}
