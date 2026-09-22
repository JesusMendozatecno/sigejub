<?php
// Modelo de Grados de la UPTYAB.
// Catálogo de grados jerárquicos que determinan el nivel y tabulador salarial del trabajador.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
    protected $fillable = ['nombre', 'codigo', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function trabajadores(): HasMany
    {
        return $this->hasMany(Trabajador::class, 'grado_id');
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
