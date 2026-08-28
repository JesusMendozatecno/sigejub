<?php

namespace App\Http\Controllers;

use App\Models\Nomina;
use App\Models\Trabajador;
use Illuminate\Http\Request;

class NominaController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->get('periodo');

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
                    'cargo' => $t->cargo ?? '',
                    'dedicacion' => $t->dedicacion ?? '',
                    'grado_cargo' => $t->grado_cargo ?? '',
                    'sueldo_base' => (float) ($pivot->sueldo_base ?? $t->sueldo_base ?? 0),
                    'tiene_nomina' => $pivot ? true : false,
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
            });

        return response()->json([
            'trabajadores' => $trabajadores,
            'nomina_id' => $nomina?->id,
            'nomina_codigo' => $nomina?->codigo,
            'nomina_estado' => $nomina?->estado,
        ]);
    }
}
