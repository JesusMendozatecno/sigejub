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
        // El expediente ya no exige solicitud: se incluyen los que no tienen
        // solicitud y los que la tienen aprobada. Los expedientes cuya solicitud
        // fue rechazada quedan fuera del cálculo.
        $expedientes = Expediente::with(['trabajador.prestacion', 'solicitud', 'clausulas'])
            ->whereHas('trabajador')
            ->where(function ($q) {
                $q->whereNull('solicitud_id')
                  ->orWhereHas('solicitud', fn($s) => $s->where('estado', 'aprobado'));
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
                    'tipo_jubilacion' => $exp->solicitud->tipo_jubilacion ?? 'Sin solicitud',
                    'clausulas' => $exp->clausulas->count(),
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
        // Se permite abrir expedientes de trabajadores dados de baja (histórico).
        $trabajador = Trabajador::withTrashed()->findOrFail($id);
        $expediente = Expediente::with('clausulas')->where('trabajador_id', $id)->firstOrFail();
        // La solicitud es opcional: un expediente puede abrirse sin solicitud.
        $solicitud = $expediente->solicitud_id ? Solicitud::find($expediente->solicitud_id) : null;
        $prestacion = Prestacion::where('trabajador_id', $id)->first();
        $primas = \App\Models\Prima::activos()->get();

        $tasaActual = TasaCambio::obtenerTasaActual();

        // Cláusulas de la convención colectiva aplicables al expediente con el
        // monto que cada una aporta al cálculo (0 en las informativas).
        $sueldoBaseT = (float) ($trabajador->sueldo_base ?? 0);
        $aniosT = (float) ($trabajador->total_anos_servicio ?? 0);
        $clausulas = $expediente->clausulas->map(function ($c) use ($sueldoBaseT, $aniosT) {
            return [
                'id' => $c->id,
                'numero' => $c->numero,
                'nombre' => $c->nombre,
                'descripcion' => $c->descripcion,
                'tipo_calculo' => $c->tipo_calculo,
                'etiqueta_tipo' => \App\Models\ClausulaConvencion::TIPOS[$c->tipo_calculo] ?? $c->tipo_calculo,
                'valor' => (float) $c->valor,
                'por_anio' => (bool) $c->por_anio,
                'observaciones' => $c->pivot->observaciones ?? null,
                'monto_referencia' => $c->monto($sueldoBaseT, $sueldoBaseT, $aniosT),
            ];
        })->values();

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
                'actividad_universitaria_info' => \Illuminate\Support\Facades\DB::table('actividades_universitarias')->where('trabajador_id', $trabajador->id)->first(),
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
                'id' => $solicitud?->id,
                'tipo_jubilacion' => $solicitud->tipo_jubilacion ?? 'Sin solicitud',
                'estado' => $solicitud->estado ?? null,
                'periodo' => $solicitud?->periodo,
                'fecha_solicitud' => $solicitud?->fecha_solicitud,
            ],
            'clausulas' => $clausulas,
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
                'fideicomiso_monto' => $prestacion->fideicomiso_monto !== null ? (float) $prestacion->fideicomiso_monto : null,
                'fideicomiso_observaciones' => $prestacion->fideicomiso_observaciones,
                'fideicomiso_documento' => $prestacion->fideicomiso_documento,
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
            'total_clausulas' => 'nullable|numeric|min:0',
            'porcentaje_jubilacion' => 'nullable|numeric',
            'anio' => 'required|integer|between:1900,' . date('Y'),
            'mes' => 'nullable|integer|min:1|max:12',
            'fideicomiso_monto' => 'nullable|numeric|min:0|max:999999999999',
            'fideicomiso_observaciones' => 'nullable|string|max:2000',
        ]);

        $t = Trabajador::find($request->trabajador_id);

        if (!$t) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Trabajador no encontrado.',
            ], 422);
        }

        if (!ValidationService::trabajadorAptoParaPrestaciones((int) $request->trabajador_id)) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'El trabajador debe tener un expediente digital y su solicitud no puede estar rechazada.',
            ], 422);
        }

        $t->update(['sueldo_base' => $request->sueldo_base]);

        $tasaActual = TasaCambio::obtenerTasaActual();

        $dataPrestacion = [
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
        ];

        // R4: monto de fideicomiso manual (se guarda aparte del monto de prestaciones)
        if ($request->has('fideicomiso_monto')) {
            $dataPrestacion['fideicomiso_monto'] = $request->filled('fideicomiso_monto')
                ? $request->fideicomiso_monto
                : null;
            $dataPrestacion['fideicomiso_observaciones'] = $request->filled('fideicomiso_observaciones')
                ? $request->fideicomiso_observaciones
                : null;
        }

        $prestacion = Prestacion::updateOrCreate(
            ['trabajador_id' => $request->trabajador_id],
            $dataPrestacion
        );

        $detalles = $request->detalles ?? [];
        $anioPrestacion = (string) $request->anio;
        $mesPrestacion = $request->filled('mes') ? (int) $request->mes : null;

        // La planilla es mensual: el código incluye el mes cuando se indicó.
        $codigoNomina = $mesPrestacion
            ? 'NOM-' . $anioPrestacion . '-' . str_pad((string) $mesPrestacion, 2, '0', STR_PAD_LEFT)
            : 'NOM-' . $anioPrestacion;

        $nomina = Nomina::firstOrCreate(
            ['periodo' => $anioPrestacion, 'mes' => $mesPrestacion],
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

        $periodoNomina = $mesPrestacion ? $anioPrestacion . ' · ' . (Nomina::MESES[$mesPrestacion] ?? '') : $anioPrestacion;

        return response()->json([
            'mensaje' => 'Prestaciones guardadas y nómina ' . $periodoNomina . ' generada correctamente.',
            'prestacion' => $prestacion,
        ]);
    }

    /**
     * ADJUNTAR el comprobante/documento del fideicomiso a la prestación (R4)
     * $id = trabajador_id
     */
    public function subirDocumentoFideicomiso(Request $request, $id)
    {
        $prestacion = Prestacion::where('trabajador_id', $id)->firstOrFail();

        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:102400',
        ]);

        $viejo = $prestacion->fideicomiso_documento;
        $path = $request->file('archivo')->store('prestaciones/fideicomiso', 'public');
        app(\App\Services\ArchivoDbService::class)->respaldar($path, 'public', $request->file('archivo')->getClientOriginalName());
        $prestacion->update(['fideicomiso_documento' => $path]);

        if ($viejo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($viejo);
            app(\App\Services\ArchivoDbService::class)->eliminar($viejo, 'public');
        }

        Activity::log('updated', 'prestacion', $prestacion->id,
            "Se adjuntó el comprobante de fideicomiso del trabajador ID {$id}");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Comprobante de fideicomiso adjuntado correctamente.',
            'archivo' => $path,
        ]);
    }

    public function comprobante(Request $request, $id)
    {
        $trabajador = Trabajador::findOrFail($id);
        $expediente = Expediente::where('trabajador_id', $id)->firstOrFail();
        // Puede no existir solicitud: el comprobante se emite con el expediente.
        $solicitud = $expediente->solicitud_id ? Solicitud::find($expediente->solicitud_id) : null;
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
            'fideicomiso_monto' => $prestacion ? (float) ($prestacion->fideicomiso_monto ?? 0) : 0,
            'fideicomiso_observaciones' => $prestacion?->fideicomiso_observaciones,
        ]);

        return $pdf->download('comprobante_prestaciones_' . $trabajador->cedula . '.pdf');
    }
}
