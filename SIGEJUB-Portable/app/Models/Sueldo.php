<?php
// Modelo de Tabulador Salarial de la UPTYAB.
// Asocia grados, niveles de instrucción y contratos con sus respectivos sueldos base
// y complementos de prima de cargo. Base para el cálculo de prestaciones.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sueldo extends Model
{
    protected $fillable = [
        'grado_id',
        'nivel_instruccion_id',
        'sueldo_base',
        'complemento_prima_cargo',
        'porcentaje_prima_cargo',
        'activo',
    ];

    protected $casts = [
        'sueldo_base' => 'decimal:2',
        'complemento_prima_cargo' => 'decimal:2',
        'porcentaje_prima_cargo' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    public function nivelInstruccion(): BelongsTo
    {
        return $this->belongsTo(NivelInstruccion::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
