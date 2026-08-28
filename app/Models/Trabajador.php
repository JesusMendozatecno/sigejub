<?php
// Modelo principal de trabajadores de la UPTYAB.
// Contiene todos los datos personales, laborales y salariales. Usa SoftDeletes para bajas lógicas.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trabajador extends Model
{
    use SoftDeletes;

    protected $table = 'trabajadores';

    protected $appends = ['estatus'];

    protected $fillable = [
        'cedula',
        'nombres',
        'apellidos',
        'cuenta_bancaria',
        'genero',
        'grado_nivel',
        'cargo',
        'unidad_departamento',
        'codigo_empleado',
        'sueldo_base',
        'denominacion_salario',
        'tabulador',
        'porcentaje_prima_cargo',
        'complemento_prima_cargo',
        'es_jefe_coordinador',
        'cesta_ticket',
        'prima_profesionalizacion',
        'sugau',
        'afiliacion_sifaiuty',
        'fecha_nacimiento',
        'edad',
        'fecha_ingreso',
        'anos_servicio_inst',
        'anos_servicio_externo',
        'total_anos_servicio',
        'nivel_instruccion',
        'nivel_educativo_texto',
        'numero_hijos',
        'hijos_discapacidad',
        'actividad_universitaria',
        'porcentaje_antiguedad',
        'porcentaje_caja_ahorro',
        'asignacion',
        'cargo_id',
        'area_id',
        'grado_id',
        'nivel_instruccion_id',
        'tipo_contrato_id',
        'dedicacion',
        'grado_cargo',
    ];

    protected $casts = [
        'asignacion' => 'string',
        'actividad_universitaria' => 'boolean',
    ];

    public function getEstatusAttribute()
    {
        if ($this->total_anos_servicio >= 25 || $this->edad >= 60) {
            return 'jubilado';
        }
        return 'activo';
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'trabajador_id');
    }

    public function expediente(): HasOne
    {
        return $this->hasOne(Expediente::class);
    }

    public function nominas(): BelongsToMany
    {
        return $this->belongsToMany(Nomina::class, 'nomina_trabajador')
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

    public function prestacion(): HasOne
    {
        return $this->hasOne(Prestacion::class, 'trabajador_id');
    }

    public function cargoRelacion(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function gradoRelacion(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function nivelInstruccionRelacion(): BelongsTo
    {
        return $this->belongsTo(NivelInstruccion::class, 'nivel_instruccion_id');
    }

    public function tipoContrato(): BelongsTo
    {
        return $this->belongsTo(TipoContrato::class, 'tipo_contrato_id');
    }
}