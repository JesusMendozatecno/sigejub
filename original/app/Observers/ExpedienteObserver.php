<?php
// Observador del modelo Expediente.
// Invalida el caché de estadísticas del dashboard cuando se crea, actualiza o elimina un expediente.

namespace App\Observers;

use App\Models\Expediente;
use App\Services\DashboardCache;

class ExpedienteObserver
{
    public function created(Expediente $expediente): void
    {
        DashboardCache::flushStats();
    }

    public function updated(Expediente $expediente): void
    {
        DashboardCache::flushStats();
    }

    public function deleted(Expediente $expediente): void
    {
        DashboardCache::flushStats();
    }
}
