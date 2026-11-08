<?php
// Modelo del catálogo de cláusulas de la convención colectiva.
// Cada cláusula define cómo se calcula su beneficio dentro de las prestaciones:
// porcentaje del sueldo base, porcentaje del sueldo integral, días de salario
// integral, monto fijo en bolívares o cláusula informativa (no suma montos).

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClausulaConvencion extends Model
{
    protected $table = 'clausulas_convencion';

    public const TIPOS = [
        'porcentaje_sueldo_base' => '% del sueldo base',
        'porcentaje_sueldo_integral' => '% del sueldo integral',
        'dias_sueldo_integral' => 'Días de salario integral',
        'monto_fijo' => 'Monto fijo (Bs.)',
        'informativa' => 'Informativa (no suma)',
    ];

    protected $fillable = [
        'numero',
        'nombre',
        'descripcion',
        'tipo_calculo',
        'valor',
        'por_anio',
        'activo',
    ];

    protected $casts = [
        'valor' => 'decimal:4',
        'por_anio' => 'boolean',
        'activo' => 'boolean',
    ];

    public function expedientes(): BelongsToMany
    {
        return $this->belongsToMany(Expediente::class, 'expediente_clausula', 'clausula_id', 'expediente_id')
            ->withPivot('observaciones')
            ->withTimestamps();
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Monto en bolívares que aporta la cláusula al cálculo de prestaciones.
     * Es 0 para las cláusulas informativas.
     */
    public function monto(float $sueldoBase, float $sueldoIntegral, float $aniosServicio): float
    {
        $valor = (float) $this->valor;
        $base = match ($this->tipo_calculo) {
            'porcentaje_sueldo_base' => $sueldoBase * ($valor / 100),
            'porcentaje_sueldo_integral' => $sueldoIntegral * ($valor / 100),
            'dias_sueldo_integral' => $sueldoIntegral > 0 ? ($sueldoIntegral / 30) * $valor : 0,
            'monto_fijo' => $valor,
            default => 0,
        };

        if ($this->por_anio) {
            $base *= max($aniosServicio, 0);
        }

        return round($base, 2);
    }

    public function etiqueta(): string
    {
        $numero = trim((string) $this->numero);
        return 'Cláusula ' . $numero . ' · ' . $this->nombre;
    }
}
