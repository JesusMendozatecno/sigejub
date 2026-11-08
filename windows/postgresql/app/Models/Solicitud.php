<?php
// Modelo para solicitudes de jubilación.
// Gestiona el flujo de solicitudes: pendiente, revisión, aprobado, rechazado.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'trabajador_nombres',
        'trabajador_apellidos',
        'trabajador_cedula',
    ];

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class)->withTrashed();
    }

    public function getTrabajadorNombreCompletoAttribute(): string
    {
        if ($this->trabajador) {
            return trim(($this->trabajador->nombres ?? '') . ' ' . ($this->trabajador->apellidos ?? ''));
        }
        $snap = trim(($this->trabajador_nombres ?? '') . ' ' . ($this->trabajador_apellidos ?? ''));
        return $snap !== '' ? $snap : '—';
    }

    public function getTrabajadorCedulaAttribute(): ?string
    {
        if ($this->trabajador) {
            return $this->trabajador->cedula ?? null;
        }
        return $this->attributes['trabajador_cedula'] ?? null;
    }

    public function expediente(): HasOne
    {
        return $this->hasOne(Expediente::class);
    }
}
