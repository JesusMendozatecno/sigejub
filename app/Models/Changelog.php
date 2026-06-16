<?php

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
