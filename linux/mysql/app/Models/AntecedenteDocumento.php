<?php
// Modelo de documento de respaldo de un antecedente de servicio: registra la
// providencia, fecha de emisión, folio, observaciones y archivo digital.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntecedenteDocumento extends Model
{
    protected $table = 'antecedente_documentos';

    protected $fillable = [
        'antecedente_id',
        'tipo_documento',
        'numero_providencia',
        'fecha_emision',
        'institucion_emisora',
        'folio_numero',
        'folio_hojas',
        'observaciones',
        'archivo',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'folio_hojas' => 'integer',
    ];

    public function antecedente(): BelongsTo
    {
        return $this->belongsTo(AntecedenteServicio::class, 'antecedente_id');
    }
}
