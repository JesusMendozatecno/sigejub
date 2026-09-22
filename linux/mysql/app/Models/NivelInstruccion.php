<?php
// Modelo de Niveles de Instrucción de la UPTYAB.
// Catálogo de niveles educativos (primaria, secundaria, universitario, etc.) que afectan el cálculo de prestaciones.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NivelInstruccion extends Model
{
    protected $table = 'niveles_instruccion';

    protected $fillable = ['nombre', 'codigo', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function trabajadores(): HasMany
    {
        return $this->hasMany(Trabajador::class, 'nivel_instruccion_id');
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
