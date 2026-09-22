<?php
// Modelo de Tipos de Contrato de la UPTYAB.
// Catálogo de tipos de contrato laboral (indefinido, temporal, consulta, etc.).

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoContrato extends Model
{
    protected $table = 'tipos_contrato';

    protected $fillable = ['nombre', 'codigo', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function trabajadores(): HasMany
    {
        return $this->hasMany(Trabajador::class, 'tipo_contrato_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
