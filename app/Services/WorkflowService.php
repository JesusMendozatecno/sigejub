<?php
// Servicio de máquina de estados para solicitudes de jubilación.
// Define las transiciones válidas y evita cambios de estado inválidos.

namespace App\Services;

class WorkflowService
{
    /**
     * Transiciones válidas de solicitud.
     * Clave = estado actual, Valor = array de estados destino permitidos.
     */
    private const SOLICITUD_TRANSITIONS = [
        'pendiente'  => ['revision', 'rechazado'],
        'revision'   => ['aprobado', 'rechazado', 'pendiente'],
        'aprobado'   => ['rechazado'],
        'rechazado'  => [],
    ];

    /**
     * Verificar si una transición de solicitud es válida.
     */
    public static function canSolicitudTransition(string $from, string $to): bool
    {
        return in_array($to, self::SOLICITUD_TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Obtener transiciones válidas desde un estado dado.
     */
    public static function allowedSolicitudTransitions(string $from): array
    {
        return self::SOLICITUD_TRANSITIONS[$from] ?? [];
    }

    /**
     * Obtener la descripción legible de un cambio de estado.
     */
    public static function solicitudTransitionLabel(string $from, string $to): string
    {
        $labels = [
            'pendiente:revision' => 'Puso en revisión',
            'revision:aprobado' => 'Aprobó',
            'revision:rechazado' => 'Rechazó',
            'aprobado:rechazado' => 'Revocó la aprobación de',
            'revision:pendiente' => 'Devivió a pendiente',
        ];

        return $labels["{$from}:{$to}"] ?? "Cambió estado de {$from} a {$to}";
    }
}
