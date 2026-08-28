<?php
// Comando Artisan para sincronizar la tasa de cambio USD/VES desde la API externa.
// Programado para ejecutarse periódicamente vía scheduler.

namespace App\Console\Commands;

use App\Services\TasaCambioService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SincronizarTasaCambio extends Command
{
    protected $signature = 'tasa:cambio:sincronizar
                            {--forzar : Forzar sincronización ignorando cache}';

    protected $description = 'Sincronizar tasa de cambio USD/VES desde API externa (BCV/Monitor)';

    public function handle(): int
    {
        $this->info('Consultando API de tasa de cambio...');

        if ($this->option('forzar')) {
            Cache::forget('sigejub.tasa_dolar.actual');
        }

        $resultado = TasaCambioService::sincronizarTasa(null);

        if ($resultado['success']) {
            $tasa = $resultado['tasa'];
            $this->info("✓ Tasa actualizada: {$tasa->tasa} {$tasa->moneda_destino}/{$tasa->moneda_origen}");
            $this->info("  Fuente: {$tasa->fuente}");
            $this->info("  Fecha: {$tasa->created_at->format('d/m/Y H:i:s')}");
            return Command::SUCCESS;
        }

        $this->warn("⚠ {$resultado['mensaje']}");
        return Command::FAILURE;
    }
}
