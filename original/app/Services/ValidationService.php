<?php
// Servicio de validaciones de negocio del sistema SIGEJUB.
// Centraliza todas las reglas de negocio que cruzan múltiples entidades.

namespace App\Services;

use App\Models\Solicitud;
use App\Models\Expediente;

class ValidationService
{
    /**
     * Verificar que un trabajador no tenga solicitud activa (pendiente/revision/aprobado).
     */
    public static function trabajadorSinSolicitudActiva(int $trabajadorId): bool
    {
        return !Solicitud::where('trabajador_id', $trabajadorId)
            ->whereIn('estado', ['pendiente', 'revision', 'aprobado'])
            ->exists();
    }

    /**
     * Verificar que un trabajador tenga solicitud aprobada.
     */
    public static function trabajadorConSolicitudAprobada(int $trabajadorId): bool
    {
        return Solicitud::where('trabajador_id', $trabajadorId)
            ->where('estado', 'aprobado')
            ->exists();
    }

    /**
     * Verificar que un trabajador no tenga expediente ya creado.
     */
    public static function trabajadorSinExpediente(int $trabajadorId): bool
    {
        return !Expediente::where('trabajador_id', $trabajadorId)->exists();
    }
}
