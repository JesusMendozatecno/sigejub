<?php
// Modelo de Áreas Organizacionales de la UPTYAB.
// Catálogo de áreas/departamentos a los que pertenecen los trabajadores.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = ['nombre', 'codigo', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function trabajadores(): HasMany
    {
        return $this->hasMany(Trabajador::class, 'area_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
