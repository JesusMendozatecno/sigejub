<?php

namespace App\Http\Controllers;

use App\Services\NominaExportService;
use App\Services\NominaImportService;
use Illuminate\Http\Request;

class NominaExportController extends Controller
{
    public function exportar(Request $request, NominaExportService $service)
    {
        $fecha = now()->format('Y-m');
        $tipo = $request->get('tipo_nomina');
        $path = $service->exportar(tipo: $tipo);

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
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $file = $request->file('archivo');

            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filename = 'nomina_import_' . uniqid() . '.xlsx';
            $file->move($tempDir, $filename);

            $results = $service->importar($tempDir . '/' . $filename);

            @unlink($tempDir . '/' . $filename);

            $mensaje = "Importación completada: {$results['registrados']} registrados, {$results['actualizados']} actualizados, {$results['omitidos']} omitidos.";
            if (!empty($results['errores'])) {
                $mensaje .= " " . count($results['errores']) . " error(es) encontrados.";
            }

            return response()->json([
                'estado' => 'success',
                'mensaje' => $mensaje,
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
