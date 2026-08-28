<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TasaCambio extends Model
{
    use HasFactory;

    protected $table = 'tasas_cambio';

    protected $fillable = [
        'tasa',
        'moneda_origen',
        'moneda_destino',
        'fuente',
        'tipo',
        'observacion',
        'usuario_id',
        'activa',
    ];

    protected $casts = [
        'tasa' => 'decimal:4',
        'activa' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopeRecientes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public static function obtenerTasaActual(): ?self
    {
        return static::activas()->recientes()->first();
    }
}
