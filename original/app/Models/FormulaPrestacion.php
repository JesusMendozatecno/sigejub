<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormulaPrestacion extends Model
{
    use HasFactory;

    protected $table = 'formulas_prestaciones';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'conceptos',
        'variables',
        'formula_matematica',
        'explicacion_variables',
        'ejemplo_calculo',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'conceptos' => 'array',
        'variables' => 'array',
        'explicacion_variables' => 'array',
        'activo' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
