<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trabajador;
use App\Models\Activity;
use Carbon\Carbon;

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

        $trabajadores = $query->orderBy('apellidos', 'asc')
                              ->orderBy('nombres', 'asc')
                              ->paginate($request->get('per_page', 10));

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
                'cedula' => 'required|unique:trabajadores,cedula',
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'genero' => 'required|in:M,F',
                'cargo' => 'required|string',
                'unidad_departamento' => 'required|string',
                'grado_nivel' => 'required|string|max:50',
                'fecha_ingreso' => 'required|date',
                'fecha_nacimiento' => 'required|date',
                'anos_servicio_externo' => 'nullable|integer',
                'nivel_instruccion' => 'nullable|integer',
                'cuenta_bancaria' => 'nullable|string|max:20',
                'numero_hijos' => 'nullable|integer',
                'hijos_discapacidad' => 'nullable|integer',
                'porcentaje_antiguedad' => 'nullable|numeric',
                'porcentaje_caja_ahorro' => 'nullable|numeric',
            ]);

            $datos = $validated;

            // --- VALORES POR DEFECTO PARA CAMPOS OPCIONALES ---
            $datos['numero_hijos'] = $datos['numero_hijos'] ?? 0;
            $datos['hijos_discapacidad'] = $datos['hijos_discapacidad'] ?? 0;
            $datos['porcentaje_caja_ahorro'] = $datos['porcentaje_caja_ahorro'] ?? 0;

            // --- CÁLCULOS AUTOMÁTICOS ---
            $datos['edad'] = Carbon::parse($request->fecha_nacimiento)->age;
            $datos['anos_servicio_inst'] = Carbon::parse($request->fecha_ingreso)->diffInYears(now());
            $datos['total_anos_servicio'] = $datos['anos_servicio_inst'] + ($request->anos_servicio_externo ?? 0);

            $trabajador = Trabajador::create($datos);

            Activity::log('created', 'trabajador', $trabajador->id,
                "Se registró al trabajador {$trabajador->nombres} {$trabajador->apellidos}");

            return response()->json([
                'status' => 'success',
                'message' => 'Trabajador registrado exitosamente en Sigejub.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error en la base de datos: ' . $e->getMessage()
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
                'cedula' => 'required|unique:trabajadores,cedula,' . $trabajador->id,
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'genero' => 'required|in:M,F',
                'cargo' => 'required|string',
                'unidad_departamento' => 'required|string',
                'grado_nivel' => 'required|string|max:50',
                'fecha_ingreso' => 'required|date',
                'fecha_nacimiento' => 'required|date',
                'anos_servicio_externo' => 'nullable|integer',
                'nivel_instruccion' => 'nullable|integer',
                'cuenta_bancaria' => 'nullable|string|max:20',
                'numero_hijos' => 'nullable|integer',
                'hijos_discapacidad' => 'nullable|integer',
                'porcentaje_antiguedad' => 'nullable|numeric',
                'porcentaje_caja_ahorro' => 'nullable|numeric',
            ]);

            $datos = $validated;

            // --- VALORES POR DEFECTO PARA CAMPOS OPCIONALES ---
            $datos['numero_hijos'] = $datos['numero_hijos'] ?? 0;
            $datos['hijos_discapacidad'] = $datos['hijos_discapacidad'] ?? 0;
            $datos['porcentaje_caja_ahorro'] = $datos['porcentaje_caja_ahorro'] ?? 0;

            // Recalcular si cambió fecha de nacimiento o ingreso
            $datos['edad'] = Carbon::parse($request->fecha_nacimiento)->age;
            $datos['anos_servicio_inst'] = Carbon::parse($request->fecha_ingreso)->diffInYears(now());
            $datos['total_anos_servicio'] = $datos['anos_servicio_inst'] + ($request->anos_servicio_externo ?? 0);

            $trabajador->update($datos);

            Activity::log('updated', 'trabajador', $trabajador->id,
                "Se actualizó el expediente de {$trabajador->nombres} {$trabajador->apellidos}");

            return response()->json([
                'status' => 'success',
                'message' => 'Datos del trabajador actualizados correctamente.',
                'trabajador' => $datos
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas del dashboard de trabajadores
     */
    public function dashboardStats()
    {
        $totalTrabajadores = Trabajador::count();

        // Próximas jubilaciones: ≥55 años o ≥20 años de servicio, pero aún no jubilables
        $proximas = Trabajador::where(function ($q) {
            $q->where('edad', '>=', 55)->where('edad', '<', 60)
              ->orWhere('total_anos_servicio', '>=', 20)->where('total_anos_servicio', '<', 25);
        })->where(function ($q) {
            $q->where('total_anos_servicio', '<', 25)
              ->where('edad', '<', 60);
        })->take(10)->get(['id', 'nombres', 'apellidos', 'edad', 'total_anos_servicio']);

        // Estatus de datos: expedientes vs total
        $totalExpedientes = \App\Models\Expediente::count();
        $porcentaje = $totalTrabajadores > 0 ? round(($totalExpedientes / $totalTrabajadores) * 100, 1) : 0;

        // Expedientes completos (estado_global = 100)
        $completos = \App\Models\Expediente::where('estado_global', 100)->count();
        $porcentajeCompletos = $totalExpedientes > 0 ? round(($completos / $totalExpedientes) * 100, 1) : 0;

        return response()->json([
            'proximas' => $proximas,
            'total_trabajadores' => $totalTrabajadores,
            'total_expedientes' => $totalExpedientes,
            'porcentaje_expedientes' => $porcentaje,
            'expedientes_completos' => $completos,
            'porcentaje_completos' => $porcentajeCompletos,
        ]);
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
                'status' => 'success',
                'message' => 'Trabajador eliminado correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

}