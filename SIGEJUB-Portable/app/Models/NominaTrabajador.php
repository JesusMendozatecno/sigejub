<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class NominaTrabajador extends Pivot
{
    protected $table = 'nomina_trabajador';

    protected $fillable = [
        'nomina_id',
        'trabajador_id',
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

    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class);
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }
}
