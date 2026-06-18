<?php
// Rutas de consola para Artisan.
// Comando inspire como placeholder, los comandos personalizados se definen en app/Console/Commands.

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
