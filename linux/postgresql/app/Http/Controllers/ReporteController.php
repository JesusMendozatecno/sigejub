<?php
// Controlador de reportes institucionales imprimibles (R5 y R6).
// R5 — Próximos jubilados: fecha estimada de retiro según el primer requisito
//       que se cumpla (60 años de edad o 25 años de servicio) y años de
//      servicio calculados con los antecedentes de servicio registrados.
// R6 — Relación de Cargos y Tiempo de Servicio: historial de cargos y
//      períodos de un trabajador con sus tiempos.

namespace App\Http\Controllers;

use App\Models\Trabajador;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    /**
     * R5 — PRÓXIMOS JUBILADOS (vista imprimible en navegador)
     */
    public function proximosJubilados(Request $request)
    {
        $desde = $request->query('desde') ?: date('Y-m-d');
        $hasta = $request->query('hasta') ?: date('Y-m-d', strtotime('+24 months'));

        try {
            $d = Carbon::parse($desde);
            $h = Carbon::parse($hasta);
        } catch (\Exception $e) {
            $d = Carbon::today();
            $h = Carbon::today()->addMonths(24);
        }

        if ($h->lt($d)) {
            [$d, $h] = [$h, $d];
        }

        // Candidatos: podrían cumplir 60 años o 25 años de servicio en el rango.
        $candidatos = Trabajador::with('antecedentesServicio')
            ->where(function ($q) use ($d, $h) {
                $q->whereBetween('fecha_nacimiento', [
                        $d->copy()->subYears(60)->toDateString(),
                        $h->copy()->subYears(60)->toDateString(),
                    ])
                    ->orWhereBetween('fecha_ingreso', [
                        $d->copy()->subYears(25)->toDateString(),
                        $h->copy()->subYears(25)->toDateString(),
                    ]);
            })
            ->get();

        $filas = $candidatos
            ->map(function ($t) {
                $porEdad = $t->fecha_nacimiento
                    ? Carbon::parse($t->fecha_nacimiento)->addYears(60)
                    : null;
                $porServicio = $t->fecha_ingreso
                    ? Carbon::parse($t->fecha_ingreso)->addYears(25)
                    : null;

                $fecha = null;
                if ($porEdad && $porServicio) {
                    $fecha = $porEdad->min($porServicio);
                } else {
                    $fecha = $porEdad ?? $porServicio;
                }
                if (!$fecha) {
                    return null;
                }

                $motivo = ($porServicio && $fecha->equalTo($porServicio) && (!$porEdad || $porServicio->lte($porEdad)))
                    ? 'Antigüedad (25 años de servicio)'
                    : 'Edad (60 años)';

                return [
                    'trabajador' => $t,
                    'fecha_estimada' => $fecha,
                    'motivo' => $motivo,
                    'anos_calculados' => $this->anosServicioCalculados($t),
                    'edad' => $t->fecha_nacimiento
                        ? Carbon::parse($t->fecha_nacimiento)->age
                        : $t->edad,
                ];
            })
            ->filter(function ($f) use ($d, $h) {
                return $f
                    && $f['fecha_estimada']->gte($d)
                    && $f['fecha_estimada']->lte($h);
            })
            ->sortBy('fecha_estimada')
            ->values();

        return view('reportes.proximos-jubilados', [
            'filas' => $filas,
            'desde' => $d->toDateString(),
            'hasta' => $h->toDateString(),
            'total' => $filas->count(),
        ]);
    }

    /**
     * R6 — RELACIÓN DE CARGOS Y TIEMPO DE SERVICIO de un trabajador
     */
    public function relacionCargos($id)
    {
        $trabajador = Trabajador::with('antecedentesServicio')->findOrFail($id);

        $condicion = $trabajador->tipo_contrato_id
            ? optional(\Illuminate\Support\Facades\DB::table('tipos_contrato')
                ->where('id', $trabajador->tipo_contrato_id)->first())->nombre
            : null;

        $periodos = $trabajador->antecedentesServicio
            ->sortByDesc('fecha_inicio')
            ->values()
            ->map(function ($p) {
                $inicio = $p->fecha_inicio ? Carbon::parse($p->fecha_inicio) : null;
                $fin = $p->fecha_fin ? Carbon::parse($p->fecha_fin) : null;
                $hasta = $fin ?? Carbon::today();
                $anios = 0;
                if ($inicio && $hasta->gte($inicio)) {
                    $anios = round($inicio->diffInMonths($hasta) / 12, 1);
                }

                return [
                    'institucion' => $p->institucion,
                    'cargo' => $p->cargo,
                    'condicion_laboral' => $p->condicion_laboral,
                    'dedicacion' => $p->dedicacion,
                    'fecha_inicio' => $p->fecha_inicio,
                    'fecha_fin' => $p->fecha_fin,
                    'vigente' => !$p->fecha_fin,
                    'es_actual' => (bool) $p->es_institucion_actual,
                    'anios' => $anios,
                    'documentos' => $p->documentos->count(),
                ];
            });

        // Total de años: suma de períodos registrados (nunca menor a los acreditados).
        $anosCalculados = $this->anosServicioCalculados($trabajador);

        return view('reportes.relacion-cargos', [
            'trabajador' => $trabajador,
            'condicion' => $condicion,
            'periodos' => $periodos,
            'anosAcreditados' => (int) ($trabajador->total_anos_servicio ?? 0),
            'anosCalculados' => $anosCalculados,
            'totalPeriodos' => $periodos->count(),
        ]);
    }

    /**
     * Años de servicio: suma de los períodos registrados en antecedentes de
     * servicio (período vigente se cuenta hasta hoy). Nunca menor a los años
     * acreditados en la ficha del trabajador.
     */
    private function anosServicioCalculados(Trabajador $t): int
    {
        $acreditados = (int) ($t->total_anos_servicio ?? 0);

        $meses = 0;
        foreach ($t->antecedentesServicio as $a) {
            if (!$a->fecha_inicio) {
                continue;
            }
            $inicio = Carbon::parse($a->fecha_inicio);
            $fin = $a->fecha_fin ? Carbon::parse($a->fecha_fin) : Carbon::today();
            if ($fin->lt($inicio)) {
                continue;
            }
            $meses += (int) $inicio->diffInMonths($fin);
        }

        $calculados = (int) floor($meses / 12);

        return max($acreditados, $calculados);
    }
}
