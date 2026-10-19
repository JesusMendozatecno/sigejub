<?php
// Rutas de consola para Artisan.
// Comandos personalizados y programación de tareas periódicas.

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincronización automática de tasa de cambio USD/VES.
// Frecuencia configurable desde .env (TASAS_INTERVALO_MINUTOS, default: 60 min).
// Se usa cron porque en esta versión no existen los métodos everyNMinutes dinámicos.
Schedule::command('tasa:cambio:sincronizar')
    ->cron('0 */' . (int) env('TASAS_INTERVALO_MINUTOS', 60) . ' * * *')
    ->withoutOverlapping()
    ->runInBackground();
