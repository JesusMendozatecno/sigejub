<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trabajador;
use App\Models\Expediente;
use App\Models\Solicitud;
use App\Models\Prestacion;

class PrestacionesController extends Controller
{
    public function index()
    {
        // Only workers who: are registered + have expediente + have approved solicitud
        $expedientes = Expediente::with(['trabajador', 'solicitud'])
            ->whereHas('solicitud', function ($q) {
                $q->where('estado', 'aprobado');
            })
            ->get()
            ->map(function ($exp) {
                $t = $exp->trabajador;
                if (!$t) return null;
                $prestacion = Prestacion::where('trabajador_id', $t->id)->first();
                return [
                    'id' => $t->id,
                    'nombres' => $t->nombres,
                    'apellidos' => $t->apellidos,
                    'cedula' => $t->cedula,
                    'total_anos_servicio' => $t->total_anos_servicio,
                    'cargo' => $t->cargo,
                    'unidad_departamento' => $t->unidad_departamento,
                    'foto_carnet' => $exp->foto_carnet,
                    'expediente_id' => $exp->id,
                    'solicitud_id' => $exp->solicitud_id,
                    'tipo_jubilacion' => $exp->solicitud->tipo_jubilacion ?? '—',
                    'tiene_prestacion' => $prestacion ? true : false,
                    'monto' => $prestacion->monto ?? null,
                    'anios_servicio_calc' => $prestacion->anios_servicio ?? null,
                ];
            })
            ->filter()
            ->values();

        return response()->json($expedientes);
    }

    public function show($id)
    {
        $trabajador = Trabajador::findOrFail($id);
        $expediente = Expediente::where('trabajador_id', $id)->firstOrFail();
        $solicitud = Solicitud::findOrFail($expediente->solicitud_id);
        $prestacion = Prestacion::where('trabajador_id', $id)->first();

        return response()->json([
            'trabajador' => [
                'id' => $trabajador->id,
                'nombres' => $trabajador->nombres,
                'apellidos' => $trabajador->apellidos,
                'cedula' => $trabajador->cedula,
                'fecha_nacimiento' => $trabajador->fecha_nacimiento,
                'edad' => $trabajador->edad,
                'cargo' => $trabajador->cargo,
                'unidad_departamento' => $trabajador->unidad_departamento,
                'fecha_ingreso' => $trabajador->fecha_ingreso,
                'total_anos_servicio' => $trabajador->total_anos_servicio,
                'numero_hijos' => $trabajador->numero_hijos ?? 0,
                'porcentaje_antiguedad' => $trabajador->porcentaje_antiguedad ?? 0,
            ],
            'expediente' => [
                'foto_carnet' => $expediente->foto_carnet,
                'estado_global' => $expediente->estado_global,
            ],
            'solicitud' => [
                'tipo_jubilacion' => $solicitud->tipo_jubilacion,
                'periodo' => $solicitud->periodo,
                'fecha_solicitud' => $solicitud->fecha_solicitud,
            ],
            'prestacion' => $prestacion ? [
                'id' => $prestacion->id,
                'monto' => $prestacion->monto,
                'anios_servicio' => $prestacion->anios_servicio,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'trabajador_id' => 'required|exists:trabajadores,id',
            'monto' => 'required|numeric|min:0',
            'anios_servicio' => 'required|integer|min:0',
        ]);

        $prestacion = Prestacion::updateOrCreate(
            ['trabajador_id' => $request->trabajador_id],
            [
                'monto' => $request->monto,
                'anios_servicio' => $request->anios_servicio,
            ]
        );

        \App\Models\Activity::log('created', 'prestacion', $prestacion->id,
            "Se registró cálculo de prestaciones para trabajador ID {$request->trabajador_id}");

        return response()->json([
            'mensaje' => 'Prestaciones calculadas correctamente.',
            'prestacion' => $prestacion,
        ]);
    }
}
