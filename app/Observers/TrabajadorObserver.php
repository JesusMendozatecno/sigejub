<?php

namespace App\Observers;

use App\Models\Trabajador;
use App\Services\DashboardCache;

class TrabajadorObserver
{
    public function created(Trabajador $trabajador): void
    {
        DashboardCache::flushStats();
    }

    public function updated(Trabajador $trabajador): void
    {
        DashboardCache::flushStats();
    }

    public function deleted(Trabajador $trabajador): void
    {
        DashboardCache::flushStats();
    }
}
