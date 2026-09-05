<?php
// Controlador de trabajadores de la UPTYAB.
// CRUD completo con SoftDeletes, cálculos automáticos de edad y años de servicio,
// filtros por estatus (activo/jubilado) y búsqueda, más estadísticas del dashboard.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trabajador;
use App\Models\Activity;
use App\Services\DashboardCache;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrabajadorController extends Controller
{
    /**
     * LISTAR trabajadorES (JSON para AJAX)
     * Con paginación y filtro opcional por búsqueda
     */
    public function index(Request $request)
    {
        $query = Trabajador::query();

        // Búsqueda por nombre, apellido o cédula
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        // Filtro: solo trabajadores sin solicitud activa o con solicitud rechazada
        if ($request->boolean('sin_solicitud_activa')) {
            $query->whereDoesntHave('solicitudes', function ($q) {
                $q->whereIn('estado', ['pendiente', 'revision', 'aprobado']);
            });
        }

        // Filtro por estatus (activo / jubilado)
        if ($estatus = $request->get('estatus')) {
            if ($estatus === 'jubilado') {
                $query->where(function ($q) {
                    $q->where('total_anos_servicio', '>=', 25)
                      ->orWhere('edad', '>=', 60);
                });
            } elseif ($estatus === 'activo') {
                $query->where(function ($q) {
                    $q->where('total_anos_servicio', '<', 25)
                      ->where('edad', '<', 60);
                });
            }
        }

        // Filtro por asignación (Manual / Nomina)
        if ($asignacion = $request->get('asignacion')) {
            $query->where('asignacion', $asignacion);
        }

        $trabajadores = $query->orderBy('nombres', 'asc')
                              ->orderBy('apellidos', 'asc')
                              ->paginate(min($request->get('per_page', 10), 100));

        return response()->json($trabajadores);
    }

    /**
     * AUTOCOMPLETE para el buscador de trabajadores en solicitudes
     * Muestra los últimos 10 registrados, o filtra por búsqueda
     */
    public function autocomplete(Request $request)
    {
        // Mostrar trabajadores sin solicitud activa o con solicitud rechazada
        $query = Trabajador::query()->whereDoesntHave('solicitudes', function ($q) {
            $q->whereIn('estado', ['pendiente', 'revision', 'aprobado']);
        });

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(nombres, ' ', apellidos)"), 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        $trabajadores = $query->orderBy('created_at', 'desc')
                              ->take(20)
                              ->get(['id', 'nombres', 'apellidos', 'cedula']);

        return response()->json($trabajadores);
    }

    /**
     * VER detalle de un trabajador individual
     */
    public function show($id)
    {
        $trabajador = Trabajador::findOrFail($id);

        return response()->json([
            'id' => $trabajador->id,
            'cedula' => $trabajador->cedula,
            'nombres' => $trabajador->nombres,
            'apellidos' => $trabajador->apellidos,
            'nombre_completo' => $trabajador->nombres . ' ' . $trabajador->apellidos,
            'genero' => $trabajador->genero,
            'cargo' => $trabajador->cargo,
            'unidad_departamento' => $trabajador->unidad_departamento,
            'grado_nivel' => $trabajador->grado_nivel,
            'fecha_nacimiento' => $trabajador->fecha_nacimiento,
            'edad' => $trabajador->edad,
            'fecha_ingreso' => $trabajador->fecha_ingreso,
            'anos_servicio_inst' => $trabajador->anos_servicio_inst,
            'anos_servicio_externo' => $trabajador->anos_servicio_externo,
            'total_anos_servicio' => $trabajador->total_anos_servicio,
            'nivel_instruccion' => $trabajador->nivel_instruccion,
            'numero_hijos' => $trabajador->numero_hijos,
            'hijos_discapacidad' => $trabajador->hijos_discapacidad,
            'actividad_universitaria' => (bool) $trabajador->actividad_universitaria,
            'cuenta_bancaria' => $trabajador->cuenta_bancaria,
            'estatus' => $trabajador->estatus,
            'porcentaje_antiguedad' => $trabajador->porcentaje_antiguedad,
            'porcentaje_caja_ahorro' => $trabajador->porcentaje_caja_ahorro,
            'created_at' => $trabajador->created_at,
            'updated_at' => $trabajador->updated_at,
        ]);
    }

    /**
     * CREAR nuevo trabajador
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'cedula' => 'required|unique:trabajadores,cedula|regex:/^[VEJPG]-?\d{5,10}$/i',
                'nombres' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'apellidos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'genero' => 'required|in:M,F',
                'cargo' => 'required|string|max:150',
                'cargo_id' => 'nullable|integer|exists:cargos,id',
                'unidad_departamento' => 'required|string|max:150',
                'grado_nivel' => 'required|string|max:50|regex:/^[A-Za-z0-9\-]+$/',
                'fecha_ingreso' => 'required|date',
                'fecha_nacimiento' => 'required|date',
                'anos_servicio_externo' => 'nullable|integer|min:0|max:60',
                'nivel_instruccion' => 'nullable|integer|min:1|max:5',
                'cuenta_bancaria' => 'nullable|string|digits:20',
                'numero_hijos' => 'nullable|integer|min:0',
                'hijos_discapacidad' => 'nullable|integer|min:0',
                'actividad_universitaria' => 'nullable|boolean',
                'porcentaje_antiguedad' => 'nullable|numeric|min:0|max:100',
                'porcentaje_caja_ahorro' => 'nullable|numeric|min:0',
            ]);

            $datos = $validated;

            if (!empty($datos['cargo_id']) && empty($datos['cargo'])) {
                $cargo = \App\Models\Cargo::find($datos['cargo_id']);
                if ($cargo) {
                    $datos['cargo'] = $cargo->nombre;
                }
            }

            $datos['numero_hijos'] = $datos['numero_hijos'] ?? 0;
            $datos['hijos_discapacidad'] = $datos['hijos_discapacidad'] ?? 0;
            $datos['actividad_universitaria'] = $datos['actividad_universitaria'] ?? false;
            $datos['porcentaje_caja_ahorro'] = $datos['porcentaje_caja_ahorro'] ?? 0;
            $datos['anos_servicio_externo'] = $datos['anos_servicio_externo'] ?? 0;
            $datos['nivel_instruccion'] = $datos['nivel_instruccion'] ?? 1;
            $datos['asignacion'] = 'Manual';

            $datos['edad'] = Carbon::parse($request->fecha_nacimiento)->age;
            $datos['anos_servicio_inst'] = Carbon::parse($request->fecha_ingreso)->diffInYears(now());
            $datos['total_anos_servicio'] = $datos['anos_servicio_inst'] + ($request->anos_servicio_externo ?? 0);

            $trabajador = Trabajador::create($datos);

            Activity::log('created', 'trabajador', $trabajador->id,
                "Se registró al trabajador {$trabajador->nombres} {$trabajador->apellidos}");

            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Trabajador registrado exitosamente en Sigejub.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'estado' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al crear trabajador: ' . $e->getMessage());
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error interno al registrar el trabajador.'
            ], 500);
        }
    }

    /**
     * EDITAR trabajador existente
     */
    public function update(Request $request, $id)
    {
        try {
            $trabajador = Trabajador::findOrFail($id);

            $validated = $request->validate([
                'cedula' => 'required|unique:trabajadores,cedula,' . $trabajador->id . '|regex:/^[VEJPG]-?\d{5,10}$/i',
                'nombres' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'apellidos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'genero' => 'required|in:M,F',
                'cargo' => 'required|string|max:150',
                'cargo_id' => 'nullable|integer|exists:cargos,id',
                'unidad_departamento' => 'required|string|max:150',
                'grado_nivel' => 'required|string|max:50|regex:/^[A-Za-z0-9\-]+$/',
                'fecha_ingreso' => 'required|date',
                'fecha_nacimiento' => 'required|date',
                'anos_servicio_externo' => 'nullable|integer|min:0|max:60',
                'nivel_instruccion' => 'nullable|integer|min:1|max:5',
                'cuenta_bancaria' => 'nullable|string|digits:20',
                'numero_hijos' => 'nullable|integer|min:0',
                'hijos_discapacidad' => 'nullable|integer|min:0',
                'actividad_universitaria' => 'nullable|boolean',
                'porcentaje_antiguedad' => 'nullable|numeric|min:0|max:100',
                'porcentaje_caja_ahorro' => 'nullable|numeric|min:0',
            ]);

            $datos = $validated;

            if (!empty($datos['cargo_id']) && empty($datos['cargo'])) {
                $cargo = \App\Models\Cargo::find($datos['cargo_id']);
                if ($cargo) {
                    $datos['cargo'] = $cargo->nombre;
                }
            }

            $datos['numero_hijos'] = $datos['numero_hijos'] ?? 0;
            $datos['hijos_discapacidad'] = $datos['hijos_discapacidad'] ?? 0;
            $datos['actividad_universitaria'] = $datos['actividad_universitaria'] ?? false;
            $datos['porcentaje_caja_ahorro'] = $datos['porcentaje_caja_ahorro'] ?? 0;
            $datos['anos_servicio_externo'] = $datos['anos_servicio_externo'] ?? ($request->anos_servicio_externo ?? 0);
            $datos['nivel_instruccion'] = $datos['nivel_instruccion'] ?? 1;

            $datos['edad'] = Carbon::parse($request->fecha_nacimiento)->age;
            $datos['anos_servicio_inst'] = Carbon::parse($request->fecha_ingreso)->diffInYears(now());
            $datos['total_anos_servicio'] = $datos['anos_servicio_inst'] + ($request->anos_servicio_externo ?? 0);

            $trabajador->update($datos);

            Activity::log('updated', 'trabajador', $trabajador->id,
                "Se actualizó el expediente de {$trabajador->nombres} {$trabajador->apellidos}");

            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Datos del trabajador actualizados correctamente.',
                'trabajador' => $datos
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'estado' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al actualizar trabajador: ' . $e->getMessage());
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error interno al actualizar el trabajador.'
            ], 500);
        }
    }

    /**
     * Estadísticas del dashboard de trabajadores
     */
    public function dashboardStats()
    {
        $data = Cache::remember(DashboardCache::key('stats.trabajadores'), DashboardCache::TTL_STATS, function () {
            $totalTrabajadores = Trabajador::count();

            $proximas = Trabajador::where(function ($q) {
                $q->where('edad', '>=', 55)->where('edad', '<', 60)
                  ->orWhere('total_anos_servicio', '>=', 20)->where('total_anos_servicio', '<', 25);
            })->where(function ($q) {
                $q->where('total_anos_servicio', '<', 25)
                  ->where('edad', '<', 60);
            })->take(10)->get(['id', 'nombres', 'apellidos', 'edad', 'total_anos_servicio', 'fecha_nacimiento', 'fecha_ingreso']);

            $proximas = $proximas->map(function ($t) {
                $porEdad = $t->fecha_nacimiento ? \Carbon\Carbon::parse($t->fecha_nacimiento)->addYears(60) : null;
                $porServicio = $t->fecha_ingreso ? \Carbon\Carbon::parse($t->fecha_ingreso)->addYears(25) : null;
                $fecha = ($porEdad && $porServicio) ? $porEdad->min($porServicio) : ($porEdad ?? $porServicio);
                $t->fecha_retiro_estimada = $fecha ? $fecha->format('Y-m-d') : null;
                return $t;
            });

            $totalExpedientes = \App\Models\Expediente::count();
            $porcentaje = $totalTrabajadores > 0 ? round(($totalExpedientes / $totalTrabajadores) * 100, 1) : 0;

            $completos = \App\Models\Expediente::where('estado_global', 100)->count();
            $porcentajeCompletos = $totalExpedientes > 0 ? round(($completos / $totalExpedientes) * 100, 1) : 0;

            return [
                'proximas' => $proximas,
                'total_trabajadores' => $totalTrabajadores,
                'total_expedientes' => $totalExpedientes,
                'porcentaje_expedientes' => $porcentaje,
                'expedientes_completos' => $completos,
                'porcentaje_completos' => $porcentajeCompletos,
            ];
        });

        return response()->json($data);
    }

    /**
     * ELIMINAR trabajador (soft delete)
     */
    public function destroy($id)
    {
        try {
            $trabajador = Trabajador::findOrFail($id);
            $trabajador->delete();

            Activity::log('deleted', 'trabajador', $id,
                "Se dio de baja al trabajador {$trabajador->nombres} {$trabajador->apellidos}");

            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Trabajador eliminado correctamente.'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al eliminar trabajador: ' . $e->getMessage());
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error interno al eliminar el trabajador.'
            ], 500);
        }
    }

}