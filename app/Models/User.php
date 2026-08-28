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
        'tipografia',
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
}
