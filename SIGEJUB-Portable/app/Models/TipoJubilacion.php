<?php
// Modelo de Tipos de Jubilación del sistema SIGEJUB.
// Catálogo de modalidades de jubilación (vejez, invalidez, muerte, etc.)
// que determinan el tipo de solicitud y cálculo de prestación.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoJubilacion extends Model
{
    protected $table = 'tipos_jubilacion';

    protected $fillable = ['nombre', 'codigo', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'tipo_jubilacion');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
