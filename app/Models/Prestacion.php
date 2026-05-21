<?php

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
