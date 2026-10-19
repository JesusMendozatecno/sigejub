<?php
// Modelo de antecedente de servicio: período laboral del trabajador dentro o
// fuera de la institución, con cargo, condición laboral, dedicación y fechas.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AntecedenteServicio extends Model
{
    protected $table = 'antecedentes_servicio';

    protected $fillable = [
        'trabajador_id',
        'institucion',
        'es_institucion_actual',
        'cargo',
        'condicion_laboral',
        'dedicacion',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
    ];

    protected $casts = [
        'es_institucion_actual' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(AntecedenteDocumento::class, 'antecedente_id');
    }
}
