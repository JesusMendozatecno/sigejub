<?php

namespace App\Http\Controllers;

use App\Services\NominaExportService;
use Illuminate\Http\Request;

class NominaExportController extends Controller
{
    public function exportar(Request $request, NominaExportService $service)
    {
        $fecha = now()->format('Y-m');
        $path = $service->exportar();

        return response()->download($path, "ADM.CONT._{$fecha}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
