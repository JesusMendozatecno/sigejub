<?php
// Servicio de validaciones de negocio del sistema SIGEJUB.
// Centraliza todas las reglas de negocio que cruzan múltiples entidades.

namespace App\Services;

use App\Models\Solicitud;
use App\Models\Expediente;
use App\Models\Prestacion;

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

    /**
     * Verificar que un trabajador tenga expediente.
     */
    public static function trabajadorConExpediente(int $trabajadorId): bool
    {
        return Expediente::where('trabajador_id', $trabajadorId)->exists();
    }

    /**
     * Verificar que un trabajador ya tenga prestación calculada.
     */
    public static function trabajadorConPrestacion(int $trabajadorId): bool
    {
        return Prestacion::where('trabajador_id', $trabajadorId)->exists();
    }

    /**
     * Validar que la cédula tenga formato válido (V-12345678, E-12345678, o numérico).
     */
    public static function cedulaValida(string $cedula): bool
    {
        return (bool) preg_match('/^[VEJPG]-?\d{5,10}$/i', $cedula);
    }

    /**
     * Obtener el ID de la solicitud activa de un trabajador.
     */
    public static function solicitudActivaId(int $trabajadorId): ?int
    {
        $solicitud = Solicitud::where('trabajador_id', $trabajadorId)
            ->whereIn('estado', ['pendiente', 'revision', 'aprobado'])
            ->latest()
            ->first();

        return $solicitud?->id;
    }
}
