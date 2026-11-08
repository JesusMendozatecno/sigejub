<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Nomina extends Model
{
    protected $table = 'nominas';

    public const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    protected $fillable = [
        'codigo',
        'periodo',
        'mes',
        'estado',
        'total_general',
    ];

    protected $casts = [
        'total_general' => 'decimal:2',
        'mes' => 'integer',
    ];

    /**
     * Nombre del mes de la planilla ("Enero", "Febrero", ...).
     * Las nóminas anuales antiguas no tienen mes y se muestran como "Anual".
     */
    public function getMesNombreAttribute(): string
    {
        if (!$this->mes) {
            return 'Anual';
        }

        return self::MESES[(int) $this->mes] ?? 'Anual';
    }

    /**
     * Código legible del período: "2026-10" o "2026" para las anuales.
     */
    public function getEtiquetaPeriodoAttribute(): string
    {
        return $this->mes
            ? $this->periodo . '-' . str_pad((string) $this->mes, 2, '0', STR_PAD_LEFT)
            : (string) $this->periodo;
    }

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
