<?php
// Modelo principal de trabajadores de la UPTYAB.
// Contiene todos los datos personales, laborales y salariales. Usa SoftDeletes para bajas lógicas.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trabajador extends Model
{
    use SoftDeletes;

    protected $table = 'trabajadores';

    protected $appends = ['estatus'];

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'trabajador_id');
    }

    public function getEstatusAttribute()
    {
        if ($this->total_anos_servicio >= 25 || $this->edad >= 60) {
            return 'jubilado';
        }
        return 'activo';
    }

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
        'porcentaje_antiguedad',
        'porcentaje_caja_ahorro',
    ];

    public function nominas()
    {
        return $this->hasMany(Nomina::class, 'trabajador_id');
    }

    public function prestacionesSociales()
    {
        return $this->hasMany(PrestacionSocial::class, 'trabajador_id');
    }
}