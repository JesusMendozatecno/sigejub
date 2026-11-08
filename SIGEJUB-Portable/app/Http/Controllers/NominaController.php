<?php
// Controlador de la nómina institucional.
// La planilla se organiza por año + mes (la institución carga la nómina mes a mes);
// las nóminas anuales antiguas (sin mes) se siguen mostrando como "Anual".
// Los conteos excluyen siempre a los trabajadores dados de baja (soft delete).

namespace App\Http\Controllers;

use App\Models\Nomina;
use App\Models\NominaTrabajador;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NominaController extends Controller
{
    public function index(Request $request)
    {
        $anio = $request->get('anio');
        $mes = $request->get('mes');
        $tipoNomina = $request->get('tipo_nomina');

        // Compatibilidad: si llega "periodo" se interpreta como año.
        if (!$anio && ($periodo = $request->get('periodo'))) {
            if (preg_match('/^(\d{4})-(\d{1,2})$/', (string) $periodo, $m)) {
                $anio = $m[1];
                $mes = $mes ?: (int) $m[2];
            } else {
                $anio = $periodo;
            }
        }

        $nomina = null;
        if ($anio) {
            $query = Nomina::with(['trabajadores' => function ($q) {
                // La relación ya excluye los trabajadores con baja lógica.
                $q->orderBy('apellidos')->orderBy('nombres');
            }])->where('periodo', (string) $anio);

            if ($mes !== null && $mes !== '') {
                $query->where('mes', (int) $mes);
            } else {
                $query->whereNull('mes');
            }
            $nomina = $query->first();
        }

        $coleccion = null;
        if ($nomina) {
            $coleccion = $nomina->trabajadores;
        } elseif (!$anio) {
            // Sin filtro de período: se listan todos los trabajadores activos.
            $coleccion = Trabajador::with('tipoContrato')
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get();
        } else {
            $coleccion = collect();
        }

        $trabajadores = $coleccion
            ->map(function ($t) use ($nomina) {
                $pivot = null;
                if ($nomina) {
                    $pivot = $nomina->trabajadores->find($t->id)?->pivot;
                }
                return [
                    'id' => $t->id,
                    'cedula' => $t->cedula,
                    'apellidos' => $t->apellidos,
                    'nombres' => $t->nombres,
                    'nombre_completo' => trim(($t->apellidos ?? '') . ' ' . ($t->nombres ?? '')),
                    'genero' => $t->genero,
                    'numero_hijos' => $t->numero_hijos ?? 0,
                    'hijos_discapacidad' => $t->hijos_discapacidad ?? 0,
                    'nivel_educativo_texto' => $t->nivel_educativo_texto ?? '',
                    'nivel_instruccion' => $t->nivel_instruccion ?? 0,
                    'fecha_ingreso' => $t->fecha_ingreso,
                    'anos_servicio_inst' => $t->anos_servicio_inst ?? 0,
                    'anos_servicio_externo' => $t->anos_servicio_externo ?? 0,
                    'total_anos_servicio' => $t->total_anos_servicio ?? 0,
                    'porcentaje_antiguedad' => (float) ($t->porcentaje_antiguedad ?? 0),
                    'codigo_prima_resp' => $t->es_jefe_coordinador ? '7' : '',
                    'tipo_nomina' => $t->tipo_nomina ?? null,
                    'cargo' => $t->cargo ?? '',
                    'dedicacion' => $t->dedicacion ?? '',
                    'grado_cargo' => $t->grado_cargo ?? '',
                    'sueldo_base' => (float) ($pivot->sueldo_base ?? $t->sueldo_base ?? 0),
                    'tiene_nomina' => $pivot ? true : false,
                    'nomina_trabajador_id' => $pivot ? (int) $pivot->id : null,
                    'prima_familiar' => $pivot ? (float) $pivot->prima_familiar : 0,
                    'prima_hijo' => $pivot ? (float) $pivot->prima_hijo : 0,
                    'prima_hijos_discapacidad' => $pivot ? (float) $pivot->prima_hijos_discapacidad : 0,
                    'prima_actividad_universitaria' => $pivot ? (float) $pivot->prima_actividad_universitaria : 0,
                    'prima_profesionalizacion' => $pivot ? (float) $pivot->prima_profesionalizacion : 0,
                    'prima_responsabilidad' => $pivot ? (float) $pivot->prima_responsabilidad : 0,
                    'complemento_prima_responsabilidad' => $pivot ? (float) $pivot->complemento_prima_responsabilidad : 0,
                    'prima_antiguedad' => $pivot ? (float) $pivot->prima_antiguedad : 0,
                    'total_asignacion' => $pivot ? (float) $pivot->total_asignacion : 0,
                ];
            })
            ->values()
            ->when($tipoNomina, fn($rows) => $rows->filter(
                fn($r) => ($r['tipo_nomina'] ?? '') === strtoupper(trim($tipoNomina))
            )->values());

        return response()->json([
            'trabajadores' => $trabajadores,
            'nomina_id' => $nomina?->id,
            'nomina_codigo' => $nomina?->codigo,
            'nomina_estado' => $nomina?->estado,
            'nomina_mes' => $nomina?->mes,
            'nomina_mes_nombre' => $nomina?->mes_nombre,
            'total_general' => $nomina ? (float) $nomina->total_general : 0,
        ]);
    }

    public function actualizarTrabajador(Request $request, int $id)
    {
        $pivot = NominaTrabajador::findOrFail($id);

        $montos = [
            'sueldo_base' => (float) ($request->input('sueldo_base') ?? 0),
            'prima_familiar' => (float) ($request->input('prima_familiar') ?? 0),
            'prima_hijo' => (float) ($request->input('prima_hijo') ?? 0),
            'prima_hijos_discapacidad' => (float) ($request->input('prima_hijos_discapacidad') ?? 0),
            'prima_actividad_universitaria' => (float) ($request->input('prima_actividad_universitaria') ?? 0),
            'prima_profesionalizacion' => (float) ($request->input('prima_profesionalizacion') ?? 0),
            'prima_responsabilidad' => (float) ($request->input('prima_responsabilidad') ?? 0),
            'complemento_prima_responsabilidad' => (float) ($request->input('complemento_prima_responsabilidad') ?? 0),
            'prima_antiguedad' => (float) ($request->input('prima_antiguedad') ?? 0),
        ];

        foreach ($montos as $campo => $valor) {
            if ($valor < 0) {
                return response()->json(['mensaje' => "El campo {$campo} no puede ser negativo."], 422);
            }
        }

        $totalAsignacion = array_sum($montos);
        $pivot->update($montos + ['total_asignacion' => $totalAsignacion]);

        $totalGeneral = $this->recalcularTotalGeneral($pivot->nomina_id);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Datos de la nómina actualizados correctamente.',
            'total_asignacion' => $totalAsignacion,
            'total_general' => $totalGeneral,
        ]);
    }

    public function eliminarTrabajador(Request $request, int $id)
    {
        $pivot = NominaTrabajador::findOrFail($id);
        $trabajador = $pivot->trabajador;
        $nominaId = $pivot->nomina_id;
        $pivot->delete();

        $totalGeneral = $this->recalcularTotalGeneral($nominaId);

        return response()->json([
            'ok' => true,
            'mensaje' => ($trabajador?->cedula ?? 'El trabajador') . ' fue eliminado de la nómina.',
            'total_general' => $totalGeneral,
        ]);
    }

    protected function recalcularTotalGeneral(int $nominaId): float
    {
        $totalGeneral = (float) DB::table('nomina_trabajador')
            ->where('nomina_id', $nominaId)
            ->sum('total_asignacion');
        Nomina::where('id', $nominaId)->update(['total_general' => $totalGeneral]);
        return $totalGeneral;
    }

    /**
     * Años y meses con nómina registrada.
     * Solo cuenta trabajadores vigentes (excluye bajas lógicas) para que el
     * panel no muestre planillas "infladas" con personal ya retirado.
     */
    public function anios()
    {
        $filas = DB::table('nominas as n')
            ->leftJoin('nomina_trabajador as nt', 'nt.nomina_id', '=', 'n.id')
            ->leftJoin('trabajadores as t', function ($join) {
                $join->on('t.id', '=', 'nt.trabajador_id')->whereNull('t.deleted_at');
            })
            ->select('n.id', 'n.periodo', 'n.mes', 'n.estado', 'n.total_general')
            ->selectRaw('COUNT(t.id) as total_trabajadores')
            ->groupBy('n.id', 'n.periodo', 'n.mes', 'n.estado', 'n.total_general')
            ->orderByDesc('n.periodo')
            ->orderByDesc('n.mes')
            ->get();

        $anios = $filas->groupBy('periodo')->map(function ($items, $periodo) {
            $meses = $items->map(function ($fila) {
                return [
                    'nomina_id' => (int) $fila->id,
                    'mes' => $fila->mes !== null ? (int) $fila->mes : null,
                    'nombre' => $fila->mes !== null ? (Nomina::MESES[(int) $fila->mes] ?? 'Anual') : 'Anual',
                    'total_trabajadores' => (int) $fila->total_trabajadores,
                    'estado' => $fila->estado,
                    'total_general' => (float) $fila->total_general,
                ];
            })
                ->sortBy(fn($m) => $m['mes'] ?? 0)
                ->values();

            $ultimo = $meses->last();

            return [
                'anio' => $periodo,
                'meses' => $meses,
                'planillas' => $meses->count(),
                // El año muestra la plantilla del mes más reciente.
                'total_trabajadores' => $ultimo['total_trabajadores'] ?? 0,
                'total_general' => $meses->sum('total_general'),
            ];
        })->values();

        return response()->json([
            'anios' => $anios,
            // Total real de trabajadores vigentes registrados en el sistema.
            'total_registrados' => Trabajador::count(),
            'meses' => Nomina::MESES,
        ]);
    }
}
