<?php
// Modelo de Primas Oficiales del sistema SIGEJUB.
// Almacena los valores oficiales de las primas (antigüedad, cargo, profesionalización, etc.)
// con su código, monto/valor y fecha de vigencia. Solo accesible por superadmin.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prima extends Model
{
    protected $fillable = ['codigo', 'nombre', 'monto', 'valor', 'fecha_vigencia', 'activo'];

    protected $casts = [
        'monto' => 'decimal:2',
        'valor' => 'decimal:2',
        'fecha_vigencia' => 'date',
        'activo' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
