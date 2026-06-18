<?php
// Modelo para prestaciones sociales detalladas (tabla "prestaciones_sociales").
// Almacena salario integral, antigüedad en días, intereses y total calculado.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestacionSocial extends Model
{
    protected $table = 'prestaciones_sociales';

    protected $fillable = [
        'trabajador_id',
        'fecha_calculo',
        'salario_integral_promedio',
        'antiguedad_dias',
        'antiguedad_monto',
        'intereses_prestaciones',
        'total_prestaciones',
    ];

    protected $casts = [
        'fecha_calculo' => 'date',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }
}
