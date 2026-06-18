<?php
// Modelo para solicitudes de jubilación.
// Gestiona el flujo de solicitudes: pendiente, revisión, aprobado, rechazado.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Solicitud extends Model
{
    use SoftDeletes;

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
