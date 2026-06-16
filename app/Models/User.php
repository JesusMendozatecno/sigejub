<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'token_recordar',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
}
