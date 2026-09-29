<?php
// Modelo de Unidades / Departamentos de la UPTYAB.
// Catálogo de unidades o departamentos a los que pertenecen los trabajadores.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidad extends Model
{
    protected $table = 'unidades';

    protected $fillable = ['nombre', 'codigo', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function trabajadores(): HasMany
    {
        return $this->hasMany(Trabajador::class, 'unidad_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}