<?php

namespace App\Http\Controllers;

use App\Models\TasaCambio;
use App\Models\Activity;
use App\Services\TasaCambioService;
use Illuminate\Http\Request;

class TasaCambioController extends Controller
{
    public function index(Request $request)
    {
        $query = TasaCambio::with('usuario');

        if ($search = $request->get('search')) {
            $query->where('fuente', 'like', "%{$search}%")
                  ->orWhere('observacion', 'like', "%{$search}%");
        }

        if ($request->boolean('solo_activas')) {
            $query->activas();
        }

        $tasas = $query->orderBy('created_at', 'desc')
            ->paginate(min($request->get('per_page', 15), 100));

        return response()->json($tasas);
    }

    public function actual()
    {
        $tasa = TasaCambio::obtenerTasaActual();

        if (!$tasa) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No hay tasas de cambio registradas.',
                'tasa' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'tasa' => [
                'id' => $tasa->id,
                'tasa' => (float) $tasa->tasa,
                'moneda_origen' => $tasa->moneda_origen,
                'moneda_destino' => $tasa->moneda_destino,
                'fuente' => $tasa->fuente,
                'tipo' => $tasa->tipo,
                'fecha' => $tasa->created_at->format('d/m/Y H:i'),
                'usuario' => $tasa->usuario ? trim($tasa->usuario->nombre . ' ' . ($tasa->usuario->apellido ?? '')) : null,
            ],
        ]);
    }

    /**
     * Estado actual de la tasa para el frontend (inicio y tasas-cambio).
     * Incluye indicador de frescura 🟢🟡🔴.
     */
    public function estado()
    {
        return response()->json(TasaCambioService::obtenerEstadoTasa());
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'tasa' => 'required|numeric|min:0.0001|max:999999.9999',
                'moneda_origen' => 'nullable|string|max:10',
                'moneda_destino' => 'nullable|string|max:10',
                'fuente' => 'nullable|string|max:100',
                'observacion' => 'nullable|string|max:500',
            ]);

            $ultima = TasaCambio::obtenerTasaActual();

            if ($ultima) {
                $ultima->update(['activa' => false]);
            }

            $tasa = TasaCambio::create([
                'tasa' => $request->tasa,
                'moneda_origen' => $request->moneda_origen ?? 'USD',
                'moneda_destino' => $request->moneda_destino ?? 'VES',
                'fuente' => $request->fuente ?? 'Manual',
                'tipo' => 'manual',
                'observacion' => $request->observacion,
                'usuario_id' => auth()->id(),
                'activa' => true,
                'fecha_consulta' => now()->toDateTimeString(),
            ]);

            TasaCambioService::limpiarCache();

            Activity::log('created', 'tasa_cambio', $tasa->id,
                "Se registró tasa de cambio manual: {$tasa->tasa} {$tasa->moneda_destino}/{$tasa->moneda_origen}");

            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Tasa de cambio registrada exitosamente.',
                'tasa' => $tasa,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['estado' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al registrar tasa: ' . $e->getMessage());
            return response()->json(['estado' => 'error', 'mensaje' => 'Error interno al registrar la tasa.'], 500);
        }
    }

    public function sincronizar()
    {
        try {
            $resultado = TasaCambioService::sincronizarTasa(auth()->id());

            return response()->json([
                'estado' => $resultado['success'] ? 'success' : 'warning',
                'mensaje' => $resultado['mensaje'],
                'tipo' => $resultado['tipo'],
                'tasa' => $resultado['tasa'],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al sincronizar tasa: ' . $e->getMessage());
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al intentar sincronizar la tasa de cambio.',
            ], 500);
        }
    }

    public function historial(Request $request)
    {
        $query = TasaCambio::with('usuario')->orderBy('created_at', 'desc');

        $tasas = $query->paginate(min($request->get('per_page', 20), 100));

        return response()->json($tasas);
    }
}
