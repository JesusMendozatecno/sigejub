<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected $fillable = [
        'trabajador_id',
        'fecha_solicitud',
        'periodo',
        'tipo_jubilacion',
        'observaciones',
        'estado',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class);
    }
}
