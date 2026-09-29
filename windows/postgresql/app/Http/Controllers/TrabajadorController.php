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

        // Filtro por tipo de nómina (ADM / DOC / OBREROS)
        if ($nomina = $request->get('nomina')) {
            $query->where('tipo_nomina', strtoupper(trim($nomina)));
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
            'unidad_id' => $trabajador->unidad_id,
            'tipo_jubilacion_id' => $trabajador->tipo_jubilacion_id,
            'tipo_jubilacion' => $trabajador->tipo_jubilacion_id
                ? optional(DB::table('tipos_jubilacion')->where('id', $trabajador->tipo_jubilacion_id)->first())->nombre
                : null,
            'estudio' => DB::table('estudios')->where('trabajador_id', $trabajador->id)->first()
                ?: null,
            'actividad_universitaria_info' => DB::table('actividades_universitarias')->where('trabajador_id', $trabajador->id)->first()
                ?: null,
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
                'cedula' => 'required|unique:trabajadores,cedula|regex:/^[VEJPG]-?\d{5,8}$/i',
                'nombres' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'apellidos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'genero' => 'required|in:M,F',
                'cargo' => 'required|string|max:150',
                'cargo_id' => 'nullable|integer|exists:cargos,id',
                'unidad_id' => 'required|integer|exists:unidades,id',
                'unidad_departamento' => 'nullable|string|max:150',
                'grado_nivel' => 'required|string|max:50|regex:/^[A-Za-z0-9\-]+$/',
                'fecha_ingreso' => 'required|date',
                'fecha_nacimiento' => 'required|date',
                'anos_servicio_externo' => 'nullable|integer|min:0|max:60',
                'nivel_instruccion' => 'nullable|integer|min:1|max:5',
                'nivel_instruccion_id' => 'nullable|integer|exists:niveles_instruccion,id',
                'tipo_jubilacion_id' => 'nullable|integer|exists:tipos_jubilacion,id',
                'especialidad' => 'nullable|string|max:150',
                'casa_estudio' => 'nullable|string|max:150',
                'casa_egreso' => 'nullable|string|max:150',
                'cuenta_bancaria' => 'nullable|string|digits:20',
                'numero_hijos' => 'nullable|integer|min:0',
                'hijos_discapacidad' => 'nullable|integer|min:0',
                'actividad_universitaria' => 'nullable|boolean',
                'act_univ_tipo' => 'nullable|string|max:150',
                'act_univ_lugar' => 'nullable|string|max:150',
                'act_univ_desde' => 'nullable|date',
                'act_univ_hasta' => 'nullable|date|after_or_equal:act_univ_desde',
                'porcentaje_antiguedad' => 'nullable|numeric|min:0|max:100',
                'porcentaje_caja_ahorro' => 'nullable|numeric|min:0',
            ]);

            $datos = $validated;

            if (!empty($datos['cargo_id'])) {
                $cargo = \App\Models\Cargo::find($datos['cargo_id']);
                if ($cargo) {
                    $datos['cargo'] = $cargo->nombre;
                }
            }

            if (!empty($datos['unidad_id'])) {
                $unidad = DB::table('unidades')->where('id', $datos['unidad_id'])->first();
                if ($unidad) {
                    $datos['unidad_departamento'] = $unidad->nombre;
                }
            }

            if (!empty($datos['nivel_instruccion_id'])) {
                $nivel = DB::table('niveles_instruccion')->where('id', $datos['nivel_instruccion_id'])->first();
                if ($nivel) {
                    $legacy = $this->nivelLegacy($nivel->nombre . ' ' . $nivel->codigo);
                    if ($legacy) {
                        $datos['nivel_instruccion'] = $legacy;
                    }
                }
            }
            $datos['nivel_instruccion'] = $datos['nivel_instruccion'] ?? 1;

            $datos['numero_hijos'] = $datos['numero_hijos'] ?? 0;
            $datos['hijos_discapacidad'] = $datos['hijos_discapacidad'] ?? 0;
            $datos['actividad_universitaria'] = $request->boolean('actividad_universitaria');
            $datos['porcentaje_caja_ahorro'] = $datos['porcentaje_caja_ahorro'] ?? 0;
            $datos['anos_servicio_externo'] = $datos['anos_servicio_externo'] ?? 0;
            $datos['asignacion'] = 'Manual';

            $estudios = [
                'nivel_instruccion_id' => $validated['nivel_instruccion_id'] ?? null,
                'especialidad' => $validated['especialidad'] ?? null,
                'casa_estudio' => $validated['casa_estudio'] ?? null,
                'casa_egreso' => $validated['casa_egreso'] ?? null,
            ];
            $actividad = [
                'actividad_universitaria' => $request->boolean('actividad_universitaria'),
                'tipo' => $validated['act_univ_tipo'] ?? null,
                'lugar' => $validated['act_univ_lugar'] ?? null,
                'fecha_desde' => $validated['act_univ_desde'] ?? null,
                'fecha_hasta' => $validated['act_univ_hasta'] ?? null,
            ];
            foreach (['especialidad', 'casa_estudio', 'casa_egreso', 'act_univ_tipo', 'act_univ_lugar', 'act_univ_desde', 'act_univ_hasta'] as $k) {
                unset($datos[$k]);
            }

            $datos['edad'] = Carbon::parse($request->fecha_nacimiento)->age;
            $datos['anos_servicio_inst'] = (int) round(Carbon::parse($request->fecha_ingreso)->diffInYears(now()));
            $datos['total_anos_servicio'] = $datos['anos_servicio_inst'] + ($request->anos_servicio_externo ?? 0);

            $trabajador = Trabajador::create($datos);

            $this->actualizarEstudios($trabajador->id, $estudios, true);
            $this->actualizarActividadesUniversitarias($trabajador->id, $actividad, true);

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
                'mensaje' => 'Error interno al registrar el trabajador.',
                'detalle' => config('app.debug') ? $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() : null,
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
                'cedula' => 'required|unique:trabajadores,cedula,' . $trabajador->id . '|regex:/^[VEJPG]-?\d{5,8}$/i',
                'nombres' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'apellidos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'genero' => 'required|in:M,F',
                'cargo' => 'required|string|max:150',
                'cargo_id' => 'nullable|integer|exists:cargos,id',
                'unidad_id' => 'required|integer|exists:unidades,id',
                'unidad_departamento' => 'nullable|string|max:150',
                'grado_nivel' => 'required|string|max:50|regex:/^[A-Za-z0-9\-]+$/',
                'fecha_ingreso' => 'required|date',
                'fecha_nacimiento' => 'required|date',
                'anos_servicio_externo' => 'nullable|integer|min:0|max:60',
                'nivel_instruccion' => 'nullable|integer|min:1|max:5',
                'nivel_instruccion_id' => 'nullable|integer|exists:niveles_instruccion,id',
                'tipo_jubilacion_id' => 'nullable|integer|exists:tipos_jubilacion,id',
                'especialidad' => 'nullable|string|max:150',
                'casa_estudio' => 'nullable|string|max:150',
                'casa_egreso' => 'nullable|string|max:150',
                'cuenta_bancaria' => 'nullable|string|digits:20',
                'numero_hijos' => 'nullable|integer|min:0',
                'hijos_discapacidad' => 'nullable|integer|min:0',
                'actividad_universitaria' => 'nullable|boolean',
                'act_univ_tipo' => 'nullable|string|max:150',
                'act_univ_lugar' => 'nullable|string|max:150',
                'act_univ_desde' => 'nullable|date',
                'act_univ_hasta' => 'nullable|date|after_or_equal:act_univ_desde',
                'porcentaje_antiguedad' => 'nullable|numeric|min:0|max:100',
                'porcentaje_caja_ahorro' => 'nullable|numeric|min:0',
            ]);

            $datos = $validated;

            if (!empty($datos['cargo_id'])) {
                $cargo = \App\Models\Cargo::find($datos['cargo_id']);
                if ($cargo) {
                    $datos['cargo'] = $cargo->nombre;
                }
            }

            if (!empty($datos['unidad_id'])) {
                $unidad = DB::table('unidades')->where('id', $datos['unidad_id'])->first();
                if ($unidad) {
                    $datos['unidad_departamento'] = $unidad->nombre;
                }
            }

            if (!empty($datos['nivel_instruccion_id'])) {
                $nivel = DB::table('niveles_instruccion')->where('id', $datos['nivel_instruccion_id'])->first();
                if ($nivel) {
                    $legacy = $this->nivelLegacy($nivel->nombre . ' ' . $nivel->codigo);
                    if ($legacy) {
                        $datos['nivel_instruccion'] = $legacy;
                    }
                }
            }
            $datos['nivel_instruccion'] = $datos['nivel_instruccion'] ?? 1;

            $datos['numero_hijos'] = $datos['numero_hijos'] ?? 0;
            $datos['hijos_discapacidad'] = $datos['hijos_discapacidad'] ?? 0;
            $datos['actividad_universitaria'] = $request->boolean('actividad_universitaria');
            $datos['porcentaje_caja_ahorro'] = $datos['porcentaje_caja_ahorro'] ?? 0;
            $datos['anos_servicio_externo'] = $datos['anos_servicio_externo'] ?? ($request->anos_servicio_externo ?? 0);

            $estudios = [
                'nivel_instruccion_id' => $validated['nivel_instruccion_id'] ?? null,
                'especialidad' => $validated['especialidad'] ?? null,
                'casa_estudio' => $validated['casa_estudio'] ?? null,
                'casa_egreso' => $validated['casa_egreso'] ?? null,
            ];
            $actividad = [
                'actividad_universitaria' => $request->boolean('actividad_universitaria'),
                'tipo' => $validated['act_univ_tipo'] ?? null,
                'lugar' => $validated['act_univ_lugar'] ?? null,
                'fecha_desde' => $validated['act_univ_desde'] ?? null,
                'fecha_hasta' => $validated['act_univ_hasta'] ?? null,
            ];
            foreach (['especialidad', 'casa_estudio', 'casa_egreso', 'act_univ_tipo', 'act_univ_lugar', 'act_univ_desde', 'act_univ_hasta'] as $k) {
                unset($datos[$k]);
            }

            $datos['edad'] = Carbon::parse($request->fecha_nacimiento)->age;
            $datos['anos_servicio_inst'] = (int) round(Carbon::parse($request->fecha_ingreso)->diffInYears(now()));
            $datos['total_anos_servicio'] = $datos['anos_servicio_inst'] + ($request->anos_servicio_externo ?? 0);

            $trabajador->update($datos);

            $this->actualizarEstudios($trabajador->id, $estudios);
            $this->actualizarActividadesUniversitarias($trabajador->id, $actividad);

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
                'mensaje' => 'Error interno al actualizar el trabajador.',
                'detalle' => config('app.debug') ? $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() : null,
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

    /**
     * Traduce un nivel de instrucción (nombre o código del maestro)
     * al código legacy 1-5 conservado en trabajadores.nivel_instruccion.
     */
    private function nivelLegacy($texto): ?int
    {
        $s = strtolower((string) $texto);
        if (str_contains($s, 'doctor')) return 5;
        if (str_contains($s, 'mag')) return 4;
        if (str_contains($s, 'especial')) return 3;
        if (str_contains($s, 'lic') || str_contains($s, 'ing')) return 2;
        if (str_contains($s, 'tsu') || str_contains($s, 'tecn')) return 1;
        return null;
    }

    /**
     * Mantiene la fila de estudios del trabajador (una por trabajador).
     */
    private function actualizarEstudios(int $trabajadorId, array $e, bool $crear = false): void
    {
        $tiene = $e['nivel_instruccion_id'] || $e['especialidad'] || $e['casa_estudio'] || $e['casa_egreso'];
        if ($tiene) {
            $fila = [
                'trabajador_id' => $trabajadorId,
                'nivel_instruccion_id' => $e['nivel_instruccion_id'],
                'especialidad' => $e['especialidad'],
                'casa_estudio' => $e['casa_estudio'],
                'casa_egreso' => $e['casa_egreso'],
                'updated_at' => now(),
            ];
            if ($crear) {
                $fila['created_at'] = now();
                DB::table('estudios')->insert($fila);
            } else {
                DB::table('estudios')->updateOrInsert(['trabajador_id' => $trabajadorId], $fila);
            }
        } else {
            DB::table('estudios')->where('trabajador_id', $trabajadorId)->delete();
        }
    }

    /**
     * Mantiene la fila de actividad universitaria del trabajador.
     */
    private function actualizarActividadesUniversitarias(int $trabajadorId, array $a, bool $crear = false): void
    {
        if ($a['actividad_universitaria']) {
            $fila = [
                'trabajador_id' => $trabajadorId,
                'tipo' => $a['tipo'],
                'lugar' => $a['lugar'],
                'fecha_desde' => $a['fecha_desde'],
                'fecha_hasta' => $a['fecha_hasta'],
                'updated_at' => now(),
            ];
            if ($crear) {
                $fila['created_at'] = now();
                DB::table('actividades_universitarias')->insert($fila);
            } else {
                DB::table('actividades_universitarias')->updateOrInsert(['trabajador_id' => $trabajadorId], $fila);
            }
        } else {
            DB::table('actividades_universitarias')->where('trabajador_id', $trabajadorId)->delete();
        }
    }

}