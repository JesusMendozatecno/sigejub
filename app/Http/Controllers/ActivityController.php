<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\DashboardCache;
use Illuminate\Support\Facades\Cache;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Cache::remember(DashboardCache::key('actividades.recientes'), 120, function () {
            return Activity::latest()->take(20)->get();
        });
        return response()->json($activities);
    }
}
