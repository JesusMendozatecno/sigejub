<?php
// Controlador de solicitudes de jubilación.
// CRUD completo con filtros por estado y búsqueda, más endpoints para estadísticas
// (solicitudes por mes, vencimientos próximos, tasa de aprobación) y exportación PDF.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;
use App\Models\Trabajador;
use App\Models\Activity;
use App\Services\DashboardCache;
use App\Services\WorkflowService;
use App\Services\NotificationService;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Cache;
// use Barryvdh\DomPDF\Facade\Pdf;

class SolicitudController extends Controller
{
    public function index(Request $request)
    {
        $query = Solicitud::with('trabajador');

        if ($estado = $request->get('estado')) {
            $estadosValidos = ['pendiente', 'aprobado', 'rechazado', 'revision'];
            $mapIngles = ['pending' => 'pendiente', 'approved' => 'aprobado', 'rejected' => 'rechazado'];
            $dbEstado = $mapIngles[$estado] ?? (in_array($estado, $estadosValidos) ? $estado : null);
            if ($dbEstado) {
                $query->where('estado', $dbEstado);
            }
        }

        if ($search = $request->get('search')) {
            $query->whereHas('trabajador', function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        $solicitudes = $query->orderBy('created_at', 'desc')
                             ->paginate(min($request->get('per_page', 10), 100));

        return response()->json($solicitudes);
    }

    public function porMes()
    {
        $datos = Cache::remember(DashboardCache::key('solicitudes.por_mes'), DashboardCache::TTL_STATS, function () {
            $driver = \DB::getDriverName();
            $mesExpr = match ($driver) {
                'pgsql' => "EXTRACT(MONTH FROM fecha_solicitud)::int",
                'sqlite' => "CAST(strftime('%m', fecha_solicitud) AS INTEGER)",
                default => 'MONTH(fecha_solicitud)',
            };

            $meses = Solicitud::selectRaw("{$mesExpr} as mes, count(*) as total")
                ->whereYear('fecha_solicitud', now()->year)
                ->groupBy('mes')
                ->orderBy('mes')
                ->pluck('total', 'mes');

            $datos = [];
            foreach (range(1, 12) as $m) {
                $datos[] = $meses->get($m, 0);
            }
            return $datos;
        });

        return response()->json($datos);
    }

    public function vencimientos()
    {
        $data = Cache::remember(DashboardCache::key('solicitudes.vencimientos'), DashboardCache::TTL_STATS, function () {
            $proximosJubilacion = Trabajador::where(function ($q) {
                $q->whereBetween('edad', [55, 59])
                  ->orWhereBetween('total_anos_servicio', [20, 24]);
            })->orderBy('edad', 'desc')->orderBy('total_anos_servicio', 'desc')
              ->limit(3)->get();

            $pendientesAntiguas = Solicitud::with('trabajador')
                ->where('estado', 'pendiente')
                ->orderBy('created_at', 'asc')
                ->limit(3)->get();

            $total = Solicitud::count();
            $aprobadas = Solicitud::where('estado', 'aprobado')->count();
            $tasaAprobacion = $total > 0 ? round(($aprobadas / $total) * 100) : 0;

            $recientes = Solicitud::with('trabajador')
                ->orderBy('created_at', 'desc')
                ->limit(5)->get();

            return [
                'proximos' => $proximosJubilacion,
                'pendientes' => $pendientesAntiguas,
                'tasa_aprobacion' => $tasaAprobacion,
                'total_solicitudes' => $total,
                'recientes' => $recientes,
            ];
        });

        return response()->json($data);
    }

    public function estadisticas()
    {
        $data = Cache::remember(DashboardCache::key('solicitudes.estadisticas'), DashboardCache::TTL_STATS, function () {
            $counts = Solicitud::selectRaw('estado, count(*) as total')
                ->groupBy('estado')
                ->pluck('total', 'estado');

            return [
                'pendiente' => $counts->get('pendiente', 0),
                'revision' => $counts->get('revision', 0),
                'aprobado' => $counts->get('aprobado', 0),
                'rechazado' => $counts->get('rechazado', 0),
                'total' => array_sum($counts->toArray()),
            ];
        });

        return response()->json($data);
    }

    public function exportarPDF(Request $request)
    {
        $query = Solicitud::with('trabajador');

        if ($estado = $request->get('estado')) {
            $estadosValidos = ['pendiente', 'aprobado', 'rechazado', 'revision'];
            $mapIngles = ['pending' => 'pendiente', 'approved' => 'aprobado', 'rejected' => 'rechazado'];
            $dbEstado = $mapIngles[$estado] ?? (in_array($estado, $estadosValidos) ? $estado : null);
            if ($dbEstado) {
                $query->where('estado', $dbEstado);
            }
        }

        $solicitudes = $query->orderBy('created_at', 'desc')->get();

        return view('pdf.solicitudes', compact('solicitudes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'trabajador_id' => 'required|exists:trabajadores,id',
                'fecha_solicitud' => 'required|date',
                'periodo' => 'nullable|string|max:20',
                'tipo_jubilacion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',
            ]);

            // Verificar si tiene solicitud rechazada → reutilizar
            $solicitudRechazada = Solicitud::where('trabajador_id', $validated['trabajador_id'])
                ->where('estado', 'rechazado')
                ->first();

            if ($solicitudRechazada) {
                $solicitudRechazada->update([
                    'fecha_solicitud' => $validated['fecha_solicitud'],
                    'periodo' => $validated['periodo'] ?? $solicitudRechazada->periodo,
                    'tipo_jubilacion' => $validated['tipo_jubilacion'] ?? $solicitudRechazada->tipo_jubilacion,
                    'observaciones' => $validated['observaciones'] ?? $solicitudRechazada->observaciones,
                    'estado' => 'pendiente',
                ]);

                $t = $solicitudRechazada->load('trabajador')->trabajador;
                $nombre = $t ? "{$t->nombres} {$t->apellidos}" : "ID {$validated['trabajador_id']}";
                Activity::log('updated', 'solicitud', $solicitudRechazada->id,
                    "Se reutilizó solicitud rechazada para {$nombre}, estado cambiado a pendiente");

                return response()->json([
                    'estado' => 'success',
                    'mensaje' => 'Solicitud rechazada reutilizada y activada exitosamente.',
                ]);
            }

            // Validación de negocio: no crear solicitud si ya hay una activa
            if (!ValidationService::trabajadorSinSolicitudActiva($validated['trabajador_id'])) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'Este trabajador ya tiene una solicitud activa (pendiente, en revisión o aprobada).',
                ], 422);
            }

            $validated['estado'] = 'pendiente';

            $solicitud = Solicitud::create($validated);

            $t = $solicitud->load('trabajador')->trabajador;
            $nombre = $t ? "{$t->nombres} {$t->apellidos}" : "ID {$validated['trabajador_id']}";
            Activity::log('created', 'solicitud', $solicitud->id,
                "Se registró una solicitud de jubilación para {$nombre}");

            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Solicitud de jubilación registrada exitosamente.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'estado' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al crear solicitud: ' . $e->getMessage());
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error interno al registrar la solicitud.'
            ], 500);
        }
    }

    public function show($id)
    {
        $solicitud = Solicitud::with('trabajador')->findOrFail($id);
        return response()->json($solicitud);
    }

    public function update(Request $request, $id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);

            $rules = [
                'trabajador_id' => 'sometimes|required|exists:trabajadores,id',
                'fecha_solicitud' => 'sometimes|required|date',
                'periodo' => 'nullable|string|max:20',
                'tipo_jubilacion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',
                'estado' => 'nullable|in:pendiente,revision,aprobado,rechazado',
            ];

            $validated = $request->validate($rules);

            // Validar transición de máquina de estados
            if (isset($validated['estado']) && $validated['estado'] !== $solicitud->estado) {
                $oldEstado = $solicitud->estado;
                $newEstado = $validated['estado'];

                if (!WorkflowService::canSolicitudTransition($oldEstado, $newEstado)) {
                    $permitidos = WorkflowService::allowedSolicitudTransitions($oldEstado);
                    return response()->json([
                        'estado' => 'error',
                        'mensaje' => "No se puede cambiar de '{$oldEstado}' a '{$newEstado}'. Transiciones permitidas: " . implode(', ', $permitidos ?: ['ninguna']),
                    ], 422);
                }
            }

            $oldEstado = $solicitud->estado;
            $solicitud->update($validated);

            $t = $solicitud->load('trabajador')->trabajador;
            $nombre = $t ? "{$t->nombres} {$t->apellidos}" : "ID {$solicitud->trabajador_id}";

            $desc = "Se actualizó la solicitud de {$nombre}";
            if (isset($validated['estado']) && $validated['estado'] !== $oldEstado) {
                $desc = WorkflowService::solicitudTransitionLabel($oldEstado, $validated['estado']) . " la solicitud de {$nombre}";

                // Enviar notificación automática al cambiar de estado
                NotificationService::solicitudTransition(
                    $solicitud->id,
                    $oldEstado,
                    $validated['estado'],
                    $nombre
                );
            }

            Activity::log('updated', 'solicitud', $solicitud->id, $desc);

            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Solicitud actualizada correctamente.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'estado' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al actualizar solicitud: ' . $e->getMessage());
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error interno al actualizar la solicitud.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $solicitud = Solicitud::with('trabajador')->findOrFail($id);
            $t = $solicitud->trabajador;
            $nombre = $t ? "{$t->nombres} {$t->apellidos}" : "ID {$solicitud->trabajador_id}";
            $solicitud->delete();

            Activity::log('deleted', 'solicitud', $id,
                "Se eliminó la solicitud de {$nombre}");

            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Solicitud eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al eliminar solicitud: ' . $e->getMessage());
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error interno al eliminar la solicitud.'
            ], 500);
        }
    }
}
