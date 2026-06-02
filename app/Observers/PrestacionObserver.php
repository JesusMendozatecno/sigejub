<?php

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
