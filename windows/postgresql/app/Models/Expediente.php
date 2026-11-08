<?php
// Modelo para expedientes de jubilación.
// Agrupa trabajador + solicitud + documentos, con un estado_global (%) que refleja el progreso documental.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        // Se incluyen trabajadores dados de baja: el expediente es histórico y
        // debe seguir mostrando el nombre aunque el trabajador ya no esté activo.
        return $this->belongsTo(Trabajador::class)->withTrashed();
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function recibos(): HasMany
    {
        return $this->hasMany(ReciboPago::class);
    }

    /**
     * Cláusulas de la convención colectiva aplicables al expediente.
     * Se usan para sumar sus beneficios al cálculo de prestaciones.
     */
    public function clausulas(): BelongsToMany
    {
        return $this->belongsToMany(
            ClausulaConvencion::class,
            'expediente_clausula',
            'expediente_id',
            'clausula_id'
        )->withPivot('observaciones')->withTimestamps();
    }
}
