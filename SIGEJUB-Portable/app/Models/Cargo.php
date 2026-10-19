<?php
// Modelo de Cargos de la UPTYAB.
// Catálogo de cargos/posiciones que ocupan los trabajadores en la organización.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    protected $fillable = ['nombre', 'codigo', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function trabajadores(): HasMany
    {
        return $this->hasMany(Trabajador::class);
    }

    public function sueldos(): HasMany
    {
        return $this->hasMany(Sueldo::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
