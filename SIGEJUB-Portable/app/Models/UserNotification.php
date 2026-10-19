<?php
// Modelo para notificaciones internas entre usuarios.
// Almacena título, mensaje, tipo y estado de lectura. Relaciona usuario emisor y receptor.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'from_user_id', 'titulo', 'mensaje', 'tipo', 'leida'
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}
