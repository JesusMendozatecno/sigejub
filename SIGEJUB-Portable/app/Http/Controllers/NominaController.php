<?php

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
        $periodo = $request->get('periodo');
        $anio = $request->get('anio');
        $tipoNomina = $request->get('tipo_nomina');
        if ($anio) {
            $periodo = $anio;
        }

        $nomina = null;
        if ($periodo) {
            $nomina = Nomina::with('trabajadores')
                ->where('periodo', $periodo)
                ->first();
        }

        $trabajadores = Trabajador::with('tipoContrato')
            ->orderBy('apellidos', 'asc')
            ->orderBy('nombres', 'asc')
            ->get()
            ->when($anio, fn($rows) => $rows->filter(fn($t) => $nomina && $nomina->trabajadores->contains('id', $t->id)))
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

    public function anios()
    {
        $anios = DB::table('nominas as n')
            ->leftJoin('nomina_trabajador as nt', 'nt.nomina_id', '=', 'n.id')
            ->select('n.periodo', 'n.id')
            ->selectRaw('COUNT(nt.trabajador_id) as total_trabajadores')
            ->groupBy('n.periodo', 'n.id')
            ->orderByDesc('n.periodo')
            ->get()
            ->groupBy('periodo')
            ->map(function ($items, $periodo) {
                return [
                    'anio' => $periodo,
                    'total_trabajadores' => $items->sum('total_trabajadores'),
                ];
            })
            ->values();

        return response()->json(['anios' => $anios]);
    }
}
