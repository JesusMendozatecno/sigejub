<?php
// Servicio de notificaciones del sistema SIGEJUB.
// Centraliza el envío de notificaciones internas y correos electrónicos
// cuando ocurren eventos importantes en el flujo de jubilación.

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use App\Models\Activity;
use App\Services\DashboardCache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Transiciones de solicitud que generan notificación.
     */
    private const SOLICITUD_TRANSITIONS = [
        'pendiente:revision' => [
            'titulo' => 'Solicitud en revisión',
            'mensaje' => 'La solicitud de {trabajador} ha pasado a revisión.',
            'tipo' => 'info',
        ],
        'revision:aprobado' => [
            'titulo' => 'Solicitud aprobada',
            'mensaje' => 'La solicitud de {trabajador} ha sido APROBADA.',
            'tipo' => 'success',
        ],
        'revision:rechazado' => [
            'titulo' => 'Solicitud rechazada',
            'mensaje' => 'La solicitud de {trabajador} ha sido RECHAZADA.',
            'tipo' => 'error',
        ],
        'aprobado:rechazado' => [
            'titulo' => 'Solicitud revocada',
            'mensaje' => 'La solicitud de {trabajador} ha sido revocada.',
            'tipo' => 'error',
        ],
    ];

    /**
     * Enviar notificación interna a un usuario específico.
     */
    public static function send(
        int $userId,
        string $titulo,
        string $mensaje,
        string $tipo = 'info'
    ): UserNotification {
        $notif = UserNotification::create([
            'user_id' => $userId,
            'from_user_id' => auth()->id(),
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
            'leida' => false,
        ]);

        DashboardCache::flushNotifications($userId);

        return $notif;
    }

    /**
     * Notificar cambio de estado de solicitud.
     * Devuelve true si se envió notificación, false si no hay plantilla para esa transición.
     */
    public static function solicitudTransition(
        int $solicitudId,
        string $oldEstado,
        string $newEstado,
        string $trabajadorNombre
    ): bool {
        $key = "{$oldEstado}:{$newEstado}";

        if (!isset(self::SOLICITUD_TRANSITIONS[$key])) {
            return false;
        }

        $tpl = self::SOLICITUD_TRANSITIONS[$key];
        $mensaje = str_replace('{trabajador}', $trabajadorNombre, $tpl['mensaje']);

        // Notificar a admins y superadmins
        $admins = User::whereIn('rol', ['admin', 'superadmin'])
            ->where('id', '!=', auth()->id())
            ->get();

        foreach ($admins as $admin) {
            self::send($admin->id, $tpl['titulo'], $mensaje, $tpl['tipo']);
        }

        return true;
    }

    /**
     * Notificar que un expediente alcanzó 100%.
     */
    public static function expedienteListo(
        int $expedienteId,
        string $trabajadorNombre
    ): void {
        $admins = User::whereIn('rol', ['admin', 'superadmin'])->get();

        foreach ($admins as $admin) {
            self::send(
                $admin->id,
                'Expediente listo para aprobación',
                "El expediente de {$trabajadorNombre} tiene todos los documentos aprobados (100%).",
                'success'
            );
        }
    }

    /**
     * Notificar que se subió carta de aprobación.
     */
    public static function cartaAprobacionSubida(
        int $expedienteId,
        string $trabajadorNombre
    ): void {
        $admins = User::whereIn('rol', ['admin', 'superadmin'])
            ->where('id', '!=', auth()->id())
            ->get();

        foreach ($admins as $admin) {
            self::send(
                $admin->id,
                'Carta de aprobación cargada',
                "Se cargó la carta de aprobación del consejo para {$trabajadorNombre}.",
                'success'
            );
        }
    }

    /**
     * Notificar que se rechazó un documento.
     */
    public static function documentoRechazado(
        int $expedienteId,
        string $documentoNombre,
        string $trabajadorNombre
    ): void {
        $admins = User::whereIn('rol', ['admin', 'superadmin'])
            ->where('id', '!=', auth()->id())
            ->get();

        foreach ($admins as $admin) {
            self::send(
                $admin->id,
                'Documento rechazado',
                "El documento '{$documentoNombre}' del expediente de {$trabajadorNombre} fue rechazado.",
                'error'
            );
        }
    }

    /**
     * Notificar prestación calculada.
     */
    public static function prestacionCalculada(
        string $trabajadorNombre,
        float $monto
    ): void {
        $admins = User::whereIn('rol', ['admin', 'superadmin'])
            ->where('id', '!=', auth()->id())
            ->get();

        $montoFmt = number_format($monto, 2, ',', '.');

        foreach ($admins as $admin) {
            self::send(
                $admin->id,
                'Prestación calculada',
                "Se calculó una prestación de \${montoFmt} para {$trabajadorNombre}.",
                'info'
            );
        }
    }

    /**
     * Enviar email con plantilla básica (wrapper de Mail::raw).
     */
    public static function sendEmail(
        User $recipient,
        string $subject,
        string $body
    ): void {
        try {
            Mail::raw($body, function ($message) use ($recipient, $subject) {
                $message->to($recipient->correo, $recipient->nombre)
                    ->subject($subject);
            });
        } catch (\Exception $e) {
            Log::warning("Error enviando email a {$recipient->correo}: " . $e->getMessage());
        }
    }
}
