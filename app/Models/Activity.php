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
        'direccion_ip', 'navegador', 'valores_anteriores', 'valores_nuevos', 'datos_peticion'
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
        $request = request();
        return self::create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'tipo_entidad' => $tipoEntidad,
            'entidad_id' => $entidadId,
            'descripcion' => $descripcion,
            'direccion_ip' => $request?->ip(),
            'navegador' => $request?->userAgent(),
            'valores_anteriores' => $valoresAnteriores,
            'valores_nuevos' => $valoresNuevos,
            'datos_peticion' => $datosPeticion,
        ]);
    }
}
