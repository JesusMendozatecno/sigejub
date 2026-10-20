<?php
// Modelo de un bloque (fragmento) del contenido binario de un archivo respaldado
// en la base de datos. Varios bloques forman el base64 completo del archivo.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoBdBloque extends Model
{
    protected $table = 'archivos_bd_bloques';

    protected $fillable = [
        'archivo_bd_id',
        'orden',
        'datos',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function archivo()
    {
        return $this->belongsTo(ArchivoBd::class, 'archivo_bd_id');
    }
}
