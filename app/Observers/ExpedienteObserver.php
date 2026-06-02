<?php

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
