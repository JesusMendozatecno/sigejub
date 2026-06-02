<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Solicitud;
use App\Models\Trabajador;
use App\Models\Expediente;
use App\Models\Prestacion;
use App\Observers\SolicitudObserver;
use App\Observers\TrabajadorObserver;
use App\Observers\ExpedienteObserver;
use App\Observers\PrestacionObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Solicitud::observe(SolicitudObserver::class);
        Trabajador::observe(TrabajadorObserver::class);
        Expediente::observe(ExpedienteObserver::class);
        Prestacion::observe(PrestacionObserver::class);
    }
}
