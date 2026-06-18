<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nomina extends Model
{
    protected $table = 'nominas';

    protected $fillable = [
        'trabajador_id',
        'periodo',
        'sueldo_base',
        'prima_familiar',
        'prima_hijo',
        'prima_hijos_discapacidad',
        'prima_actividad_universitaria',
        'prima_profesionalizacion',
        'prima_responsabilidad',
        'complemento_prima_responsabilidad',
        'prima_antiguedad',
        'cesta_ticket',
        'total_asignacion',
        'sso',
        'lpf',
        'faov',
        'aporte_ipasme',
        'aporte_caja_ahorro',
        'prestamo_caja_ahorro',
        'isr',
        'horas_extras',
        'total_deduccion',
        'neto_a_cobrar',
    ];

    protected $casts = [
        'periodo' => 'date',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }
}
