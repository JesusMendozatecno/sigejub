<?php
// Modelo de usuario del sistema SIGEJUB.
// Extiende Authenticatable de Laravel. Campos en español: nombre, apellido, correo, rol.
// Soporta temas, 2FA, notificaciones y preferencias de perfil.

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'telefono',
        'fecha_nacimiento',
        'password',
        'rol',
        'avatar',
        'tema',
        'idioma',
        'color_acento',
        'verificacion_dos_pasos',
        'secreto_2fa',
        'notificacion_correo',
        'notificacion_sistema',
        'perfil_publico',
        'ultimo_acceso',
        'ultimo_acceso_ip',
    ];

    protected $hidden = [
        'password',
        'token_recordar',
    ];

    protected function casts(): array
    {
        return [
            'correo_verificado_en' => 'datetime',
            'password' => 'hashed',
            'verificacion_dos_pasos' => 'boolean',
            'perfil_publico' => 'boolean',
            'ultimo_acceso' => 'datetime',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function receivedNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id');
    }

    public function sentNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'from_user_id');
    }

    // El esquema de la BD renombró remember_token a token_recordar.
    public function getRememberTokenName(): string
    {
        return 'token_recordar';
    }

    public function getRememberToken(): ?string
    {
        return $this->{$this->getRememberTokenName()};
    }

    public function setRememberToken($value): void
    {
        $this->{$this->getRememberTokenName()} = $value;
    }

    /**
     * Identificador usado en el flujo de recuperación de contraseña.
     * La tabla password_reset_tokens se indexa por este valor (columna email).
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    /**
     * Envía el enlace de restablecimiento de contraseña por correo.
     * Se genera con el enlace hacia la ruta password.reset y el correo como parámetro.
     */
    public function sendPasswordResetNotification($token): void
    {
        $url = url(route('password.reset', [
            'token' => $token,
            'correo' => $this->correo,
        ]));

        $body = "Hola {$this->nombre}, recibimos una solicitud para restablecer tu contraseña.\n\n";
        $body .= "Haz clic en el siguiente enlace para crear una nueva contraseña:\n{$url}\n\n";
        $body .= "Si no solicitaste este cambio, puedes ignorar este correo.\n";
        $body .= "El enlace es válido por 60 minutos.\n\nSaludos,\nEquipo SIGEJUB";

        \App\Services\NotificationService::sendEmail(
            $this,
            'Recuperación de contraseña - SIGEJUB',
            $body
        );
    }
}
