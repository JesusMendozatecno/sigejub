<?php
// Controlador de prestaciones sociales.
// Lista trabajadores con solicitud aprobada y expediente completo (100%),
// permite calcular, guardar (genera registro en nómina) y exportar.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trabajador;
use App\Models\Expediente;
use App\Models\Solicitud;
use App\Models\Prestacion;
use App\Models\Nomina;
use App\Models\Activity;
use App\Models\TasaCambio;
use App\Services\NotificationService;
use App\Services\ValidationService;
use Barryvdh\DomPDF\Facade\Pdf;

class PrestacionesController extends Controller
{
    public function index(Request $request)
    {
        $expedientes = Expediente::with(['trabajador.prestacion', 'solicitud'])
            ->whereHas('trabajador')
            ->whereHas('solicitud', function ($q) {
                $q->where('estado', 'aprobado');
            })
            ->where('estado_global', 100)
            ->paginate(min($request->get('per_page', 50), 100))
            ->through(function ($exp) {
                $t = $exp->trabajador;
                if (!$t) return null;
                $prestacion = $t->prestacion;
                return [
                    'id' => $t->id,
                    'nombres' => $t->nombres,
                    'apellidos' => $t->apellidos,
                    'cedula' => $t->cedula,
                    'total_anos_servicio' => $t->total_anos_servicio,
                    'cargo' => $t->cargo,
                    'unidad_departamento' => $t->unidad_departamento,
                    'nivel_educativo_texto' => $t->nivel_educativo_texto,
                    'nivel_instruccion_id' => $t->nivel_instruccion_id,
                    'foto_carnet' => $exp->foto_carnet,
                    'expediente_id' => $exp->id,
                    'solicitud_id' => $exp->solicitud_id,
                    'tipo_jubilacion' => $exp->solicitud->tipo_jubilacion ?? '—',
                    'tiene_prestacion' => $prestacion ? true : false,
                    'monto' => $prestacion->monto ?? null,
                    'anios_servicio_calc' => $prestacion->anios_servicio ?? null,
                ];
            });

        // Filter out null entries from paginated results
        $expedientes->setCollection(
            $expedientes->getCollection()->filter()->values()
        );

        return response()->json($expedientes);
    }

    public function show($id)
    {
        $trabajador = Trabajador::findOrFail($id);
        $expediente = Expediente::where('trabajador_id', $id)->firstOrFail();
        $solicitud = Solicitud::findOrFail($expediente->solicitud_id);
        $prestacion = Prestacion::where('trabajador_id', $id)->first();
        $primas = \App\Models\Prima::activos()->get();

        $tasaActual = TasaCambio::obtenerTasaActual();

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
                'sueldo_base' => (float) ($trabajador->sueldo_base ?? 0),
                'numero_hijos' => $trabajador->numero_hijos ?? 0,
                'hijos_discapacidad' => $trabajador->hijos_discapacidad ?? 0,
                'actividad_universitaria' => (bool) $trabajador->actividad_universitaria,
                'porcentaje_antiguedad' => (float) ($trabajador->porcentaje_antiguedad ?? 0),
                'prima_profesionalizacion' => (float) ($trabajador->prima_profesionalizacion ?? 0),
                'es_jefe_coordinador' => (bool) $trabajador->es_jefe_coordinador,
                'cesta_ticket' => (float) ($trabajador->cesta_ticket ?? 0),
                'sugau' => (float) ($trabajador->sugau ?? 0),
                'nivel_educativo_texto' => $trabajador->nivel_educativo_texto,
                'nivel_instruccion_id' => $trabajador->nivel_instruccion_id,
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
                'detalles' => $prestacion->detalles ?? null,
                'sueldo_integral' => $prestacion->sueldo_integral,
                'total_primas' => $prestacion->total_primas,
                'porcentaje_jubilacion' => $prestacion->porcentaje_jubilacion,
                'tasa_utilizada' => $prestacion->tasa_utilizada ? (float) $prestacion->tasa_utilizada : null,
                'moneda_tasa' => $prestacion->moneda_tasa,
                'fecha_tasa_utilizada' => $prestacion->fecha_tasa_utilizada,
                'fuente_tasa' => $prestacion->fuente_tasa,
                'calculado_por_user_id' => $prestacion->calculado_por_user_id,
            ] : null,
            'primas' => $primas->map(fn($p) => [
                'id' => $p->id,
                'codigo' => $p->codigo,
                'nombre' => $p->nombre,
                'valor' => (float) $p->valor,
            ]),
            'tasa_actual' => $tasaActual ? [
                'id' => $tasaActual->id,
                'tasa' => (float) $tasaActual->tasa,
                'moneda_origen' => $tasaActual->moneda_origen,
                'moneda_destino' => $tasaActual->moneda_destino,
                'fuente' => $tasaActual->fuente,
                'fecha' => $tasaActual->created_at->format('d/m/Y H:i'),
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'trabajador_id' => 'required|exists:trabajadores,id',
            'sueldo_base' => 'required|numeric|min:0',
            'monto' => 'required|numeric|min:0',
            'anios_servicio' => 'required|integer|min:0',
            'detalles' => 'nullable|array',
            'sueldo_integral' => 'nullable|numeric',
            'total_primas' => 'nullable|numeric',
            'porcentaje_jubilacion' => 'nullable|numeric',
            'anio' => 'required|integer|between:1900,' . date('Y'),
        ]);

        $t = Trabajador::find($request->trabajador_id);

        if (!$t) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Trabajador no encontrado.',
            ], 422);
        }

        if (!ValidationService::trabajadorConSolicitudAprobada($request->trabajador_id)) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Solo se pueden calcular prestaciones para trabajadores con solicitud aprobada.',
            ], 422);
        }

        $t->update(['sueldo_base' => $request->sueldo_base]);

        $tasaActual = TasaCambio::obtenerTasaActual();

        $prestacion = Prestacion::updateOrCreate(
            ['trabajador_id' => $request->trabajador_id],
            [
                'monto' => $request->monto,
                'anios_servicio' => $request->anios_servicio,
                'detalles' => $request->detalles,
                'sueldo_integral' => $request->sueldo_integral ?? 0,
                'total_primas' => $request->total_primas ?? 0,
                'porcentaje_jubilacion' => $request->porcentaje_jubilacion ?? 100,
                'tasa_cambio_id' => $tasaActual?->id,
                'tasa_utilizada' => $tasaActual?->tasa,
                'moneda_tasa' => $tasaActual ? ($tasaActual->moneda_destino . '/' . $tasaActual->moneda_origen) : null,
                'fecha_tasa_utilizada' => $tasaActual?->created_at,
                'fuente_tasa' => $tasaActual?->fuente,
                'calculado_por_user_id' => auth()->id(),
            ]
        );

        $detalles = $request->detalles ?? [];
        $anioPrestacion = (string) $request->anio;

        $codigoNomina = 'NOM-' . $anioPrestacion;

        $nomina = Nomina::firstOrCreate(
            ['periodo' => $anioPrestacion],
            ['codigo' => $codigoNomina, 'estado' => 'borrador']
        );

        $pivotData = [
            'sueldo_base' => $request->sueldo_base,
            'total_asignacion' => $request->total_primas ?? 0,
            'total_deduccion' => 0,
            'neto_a_cobrar' => ($request->sueldo_integral ?? 0),
        ];

        foreach ($detalles as $d) {
            $codigo = $d['codigo'] ?? '';
            $monto = $d['monto'] ?? 0;
            $map = [
                'PRIMA_FAMILIAR' => 'prima_antiguedad',
                'PRIMA_HIJO' => 'prima_hijo',
                'PRIMA_HIJOS_DISCAPACIDAD' => 'prima_hijos_discapacidad',
                'PRIMA_PROFESIONALIZACION' => 'prima_profesionalizacion',
                'PRIMA_RESPONSABILIDAD' => 'prima_responsabilidad',
                'PRIMA_ACTIVIDAD_UNIVERSITARIA' => 'prima_actividad_universitaria',
                'CESTA_TICKET' => 'cesta_ticket',
            ];
            if (isset($map[$codigo])) {
                $pivotData[$map[$codigo]] = $monto;
            }
        }

        $nomina->trabajadores()->syncWithoutDetaching([
            $request->trabajador_id => $pivotData
        ]);

        Activity::log('created', 'prestacion', $prestacion->id,
            "Se registró cálculo de prestaciones para trabajador ID {$request->trabajador_id}");

        $nombre = "{$t->nombres} {$t->apellidos}";
        NotificationService::prestacionCalculada($nombre, $request->monto);

        return response()->json([
            'mensaje' => 'Prestaciones guardadas y nómina ' . $anioPrestacion . ' generada correctamente.',
            'prestacion' => $prestacion,
        ]);
    }

    public function comprobante(Request $request, $id)
    {
        $trabajador = Trabajador::findOrFail($id);
        $expediente = Expediente::where('trabajador_id', $id)->firstOrFail();
        $solicitud = Solicitud::findOrFail($expediente->solicitud_id);
        $prestacion = Prestacion::where('trabajador_id', $id)->first();

        $sueldoBase = (float) ($request->sueldo_base ?? $trabajador->sueldo_base ?? 0);
        $totalPrimas = (float) ($request->total_primas ?? 0);
        $sueldoIntegral = (float) ($request->sueldo_integral ?? 0);
        $totalPrestaciones = (float) ($request->total_prestaciones ?? 0);
        $porcentaje = (float) ($request->porcentaje_jubilacion ?? 100);
        $detalles = $request->detalles ?? [];

        $fotoBase64 = null;
        $fotoPath = $expediente->foto_carnet;
        if ($fotoPath) {
            $fullPath = storage_path('app/public/' . $fotoPath);
            if (file_exists($fullPath)) {
                $fotoBase64 = 'data:image/' . pathinfo($fullPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }

        $pdf = Pdf::loadView('pdf.comprobante', [
            'trabajador' => $trabajador,
            'expediente' => $expediente,
            'solicitud' => $solicitud,
            'prestacion' => $prestacion,
            'sueldo_base' => $sueldoBase,
            'total_primas' => $totalPrimas,
            'sueldo_integral' => $sueldoIntegral,
            'total_prestaciones' => $totalPrestaciones,
            'porcentaje' => $porcentaje,
            'detalles' => $detalles,
            'foto_base64' => $fotoBase64,
        ]);

        return $pdf->download('comprobante_prestaciones_' . $trabajador->cedula . '.pdf');
    }
}
