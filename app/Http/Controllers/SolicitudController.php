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
                             ->paginate($request->get('per_page', 10));

        return response()->json($solicitudes);
    }

    public function porMes()
    {
        $datos = Cache::remember(DashboardCache::key('solicitudes.por_mes'), DashboardCache::TTL_STATS, function () {
            $meses = Solicitud::selectRaw('MONTH(fecha_solicitud) as mes, count(*) as total')
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
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al registrar: ' . $e->getMessage()
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

            $oldEstado = $solicitud->estado;
            $solicitud->update($validated);

            $t = $solicitud->load('trabajador')->trabajador;
            $nombre = $t ? "{$t->nombres} {$t->apellidos}" : "ID {$solicitud->trabajador_id}";

            $desc = "Se actualizó la solicitud de {$nombre}";
            if ($request->has('estado') && $request->estado !== $oldEstado) {
                $accion = match ($request->estado) {
                    'aprobado' => 'Aprobó',
                    'rechazado' => 'Rechazó',
                    'revision' => 'Puso en revisión',
                    default => 'Cambió estado a'
                };
                $desc = "{$accion} la solicitud de {$nombre}";
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
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al actualizar: ' . $e->getMessage()
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
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
