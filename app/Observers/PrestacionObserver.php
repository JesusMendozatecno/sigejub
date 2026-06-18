<?php
// Observador del modelo Prestacion.
// Invalida el caché de estadísticas del dashboard cuando cambia una prestación.

namespace App\Observers;

use App\Models\Prestacion;
use App\Services\DashboardCache;

class PrestacionObserver
{
    public function created(Prestacion $prestacion): void
    {
        DashboardCache::flushStats();
    }

    public function updated(Prestacion $prestacion): void
    {
        DashboardCache::flushStats();
    }

    public function deleted(Prestacion $prestacion): void
    {
        DashboardCache::flushStats();
    }
}
