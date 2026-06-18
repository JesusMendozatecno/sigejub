<?php
// Modelo para prestaciones sociales (tabla "prestaciones").
// Almacena el cálculo simple de años de servicio y monto para trabajadores con solicitud aprobada.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestacion extends Model
{
    protected $table = 'prestaciones';

    protected $fillable = [
        'trabajador_id',
        'anios_servicio',
        'monto',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class);
    }
}
