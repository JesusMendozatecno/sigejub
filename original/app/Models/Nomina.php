<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Nomina extends Model
{
    protected $table = 'nominas';

    protected $fillable = [
        'codigo',
        'periodo',
        'estado',
        'total_general',
    ];

    protected $casts = [
        'total_general' => 'decimal:2',
    ];

    public function trabajadores(): BelongsToMany
    {
        return $this->belongsToMany(Trabajador::class, 'nomina_trabajador')
            ->withPivot([
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
            ])
            ->withTimestamps();
    }
}
