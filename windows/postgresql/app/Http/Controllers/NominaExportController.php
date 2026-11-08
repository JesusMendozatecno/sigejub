<?php

namespace App\Http\Controllers;

use App\Services\NominaExportService;
use App\Services\NominaImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NominaExportController extends Controller
{
    public function exportar(Request $request, NominaExportService $service)
    {
        $tipo = $request->get('tipo_nomina');
        $anio = $request->get('anio');
        $mes = $request->get('mes') !== null && $request->get('mes') !== '' ? (int) $request->get('mes') : null;

        $fecha = $mes ? $anio . '-' . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) : ($anio ?: now()->format('Y-m'));
        $path = $service->exportar(periodo: $anio, tipo: $tipo, mes: $mes);

        $prefix = match($tipo) {
            'DOC' => 'DOC',
            'OBREROS' => 'OBR',
            default => 'ADM.CONT',
        };

        return response()->download($path, "{$prefix}._.{$fecha}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importar(Request $request, NominaImportService $service)
    {            $request->validate([
                'archivo' => 'required|file|mimes:xlsx,xls|max:10240',
                'anio' => 'required|digits:4',
                'mes' => 'nullable|integer|min:1|max:12',
            ]);

        try {
            $file = $request->file('archivo');
            $anio = $request->input('anio');
            $mes = $request->filled('mes') ? (int) $request->input('mes') : null;

            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filename = 'nomina_import_' . Str::random(40) . '.xlsx';
            $file->move($tempDir, $filename);

            $results = $service->importar($tempDir . '/' . $filename, $anio, $mes);

            @unlink($tempDir . '/' . $filename);

            $msg = "Período de la nómina: {$anio}" . ($mes ? ' · ' . $results['mes_nombre'] : '') . '<br>';
            $msg .= "Trabajadores encontrados: {$results['encontrados']}<br>";
            $msg .= "Trabajadores nuevos registrados: {$results['nuevos']}<br>";
            $msg .= "Trabajadores ya registrados: {$results['ya_existentes']}<br>";
            $msg .= "Trabajadores omitidos: {$results['omitidos']}";

            if ($results['nuevos'] === 0 && $results['encontrados'] > 0) {
                $msg .= "<br><br><em>No se agregaron registros porque todos los trabajadores ya se encuentran registrados en la Nómina {$anio}.</em>";
            }
            if (!empty($results['errores'])) {
                $msg .= '<br>' . count($results['errores']) . " error(es) encontrados.";
            }

            return response()->json([
                'estado' => 'success',
                'mensaje' => $msg,
                'datos' => $results,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al importar nómina: ' . $e->getMessage());
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al procesar el archivo de nómina.',
            ], 500);
        }
    }
}
