<?php
// Modelo de recibo de pago / comprobante de nómina adjunto al expediente.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReciboPago extends Model
{
    protected $table = 'recibos_pago';

    protected $fillable = [
        'expediente_id',
        'periodo',
        'concepto',
        'monto_usd',
        'archivo',
        'observaciones',
    ];

    protected $casts = [
        'monto_usd' => 'decimal:2',
    ];

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class);
    }
}
