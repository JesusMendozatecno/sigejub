<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trabajador extends Model
{
    use SoftDeletes;

    protected $table = 'trabajadores';

    protected $fillable = [
        'cedula',
        'nombres',
        'apellidos',
        'cuenta_bancaria',
        'genero',
        'grado_nivel',
        'cargo',
        'unidad_departamento',
        'fecha_nacimiento',
        'edad',
        'fecha_ingreso',
        'anos_servicio_inst',
        'anos_servicio_externo',
        'total_anos_servicio',
        'nivel_instruccion',
        'numero_hijos',
        'hijos_discapacidad',
        'porcentaje_antiguedad',
        'porcentaje_caja_ahorro',
    ];
}