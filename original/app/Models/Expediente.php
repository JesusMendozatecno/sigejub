<?php
// Modelo para expedientes de jubilación.
// Agrupa trabajador + solicitud + documentos, con un estado_global (%) que refleja el progreso documental.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expediente extends Model
{
    protected $fillable = [
        'trabajador_id',
        'solicitud_id',
        'foto_carnet',
        'estado_global',
        'notas_admin',
        'carta_aprobacion',
    ];

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
