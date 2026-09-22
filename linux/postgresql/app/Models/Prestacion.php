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
        'detalles',
        'sueldo_integral',
        'total_primas',
        'porcentaje_jubilacion',
        'tasa_cambio_id',
        'tasa_utilizada',
        'moneda_tasa',
        'fecha_tasa_utilizada',
        'fuente_tasa',
        'calculado_por_user_id',
    ];

    protected $casts = [
        'detalles' => 'array',
        'monto' => 'decimal:2',
        'sueldo_integral' => 'decimal:2',
        'total_primas' => 'decimal:2',
        'porcentaje_jubilacion' => 'decimal:2',
        'tasa_utilizada' => 'decimal:2',
        'fecha_tasa_utilizada' => 'datetime',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function tasaCambio()
    {
        return $this->belongsTo(TasaCambio::class, 'tasa_cambio_id');
    }

    public function calculadoPor()
    {
        return $this->belongsTo(User::class, 'calculado_por_user_id');
    }
}
