<?php
// Modelo para el registro de actividad (caja negra). 
// Almacena auditoría de todas las acciones del sistema: creación, modificación y eliminación de entidades.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'user_id', 'accion', 'tipo_entidad', 'entidad_id', 'descripcion',
        'direccion_ip', 'navegador', 'metodo', 'ruta',
        'valores_anteriores', 'valores_nuevos', 'datos_peticion'
    ];

    protected $with = ['user'];

    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
        'datos_peticion' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        string $accion,
        string $tipoEntidad,
        ?int $entidadId,
        string $descripcion,
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?array $datosPeticion = null
    ): self {
        // Delega en AuditService para registrar con sanitización (inmutable y seguro).
        return \App\Services\AuditService::registrar(
            $accion,
            $tipoEntidad,
            $entidadId,
            $descripcion,
            $valoresAnteriores,
            $valoresNuevos,
            $datosPeticion
        );
    }

    // Acción legible para el usuario (español).
    public function accionHumana(): string
    {
        return \App\Services\AuditService::accionHumana($this->accion);
    }

    // Tipo de entidad legible para el usuario (español).
    public function tipoEntidadHumana(): string
    {
        return \App\Services\AuditService::entidadHumana($this->tipo_entidad);
    }
}
