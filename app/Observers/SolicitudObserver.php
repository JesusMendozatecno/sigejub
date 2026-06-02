<?php

namespace App\Observers;

use App\Models\Solicitud;
use App\Services\DashboardCache;

class SolicitudObserver
{
    public function created(Solicitud $solicitud): void
    {
        DashboardCache::flushSolicitudes();
        DashboardCache::flushStats();
    }

    public function updated(Solicitud $solicitud): void
    {
        DashboardCache::flushSolicitudes();
    }

    public function deleted(Solicitud $solicitud): void
    {
        DashboardCache::flushSolicitudes();
        DashboardCache::flushStats();
    }
}
