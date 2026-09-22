<?php
// Modelo para el registro de cambios del sistema (changelog).
// Almacena commits de git categorizados por tipo y sección, visibles en la documentación.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Changelog extends Model
{
    protected $fillable = [
        'nombre_autor',
        'correo_autor',
        'hash_commit',
        'mensaje_commit',
        'descripcion',
        'tipo',
        'seccion',
    ];
}
