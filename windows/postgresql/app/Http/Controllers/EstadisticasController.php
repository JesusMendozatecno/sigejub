<?php
// Controlador de estadísticas del dashboard y del centro de reportes.
// Todas las cifras salen directamente de la base de datos (no se estiman):
// trabajadores vigentes, solicitudes por estatus y por tipo de jubilación,
// expedientes, nómina del último período cargado y prestaciones.

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\Nomina;
use App\Models\Prestacion;
use App\Models\Solicitud;
use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EstadisticasController extends Controller
{
    /**
     * Estadísticas en vivo (sin caché) para que los gráficos reflejen siempre
     * lo que hay en la base de datos.
     */
    public function dashboard()
    {
        return response()->json($this->construir());
    }

    private function construir(): array
    {
        // ── Trabajadores vigentes ────────────────────────────────────────────
        $totalTrabajadores = Trabajador::count();

        $porTipoNomina = Trabajador::query()
            ->selectRaw("UPPER(COALESCE(NULLIF(TRIM(tipo_nomina), ''), 'SIN TIPO')) as tipo, COUNT(*) as total")
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $porEstatusTrabajador = Trabajador::query()
            ->selectRaw('SUM(CASE WHEN total_anos_servicio >= 25 OR edad >= 60 THEN 1 ELSE 0 END) as jubilados')
            ->selectRaw('SUM(CASE WHEN total_anos_servicio < 25 AND edad < 60 THEN 1 ELSE 0 END) as activos')
            ->first();

        // ── Solicitudes ─────────────────────────────────────────────────────
        $solicitudesPorEstado = Solicitud::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $solicitudesPorTipo = Solicitud::query()
            ->selectRaw("COALESCE(NULLIF(TRIM(tipo_jubilacion), ''), 'Sin especificar') as tipo, COUNT(*) as total")
            ->groupBy('tipo')
            ->orderByDesc('total')
            ->pluck('total', 'tipo');

        // Solicitudes por mes del año en curso (general).
        $driver = DB::getDriverName();
        $mesExpr = match ($driver) {
            'pgsql' => 'EXTRACT(MONTH FROM fecha_solicitud)::int',
            'sqlite' => "CAST(strftime('%m', fecha_solicitud) AS INTEGER)",
            default => 'MONTH(fecha_solicitud)',
        };

        $porMes = Solicitud::query()
            ->selectRaw("{$mesExpr} as mes, COUNT(*) as total")
            ->whereYear('fecha_solicitud', now()->year)
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $serieMensual = [];
        foreach (range(1, 12) as $mes) {
            $serieMensual[] = (int) ($porMes->get($mes, 0));
        }

        // Detalle por tipo de jubilación y por mes (específico).
        $porTipoMes = Solicitud::query()
            ->selectRaw("COALESCE(NULLIF(TRIM(tipo_jubilacion), ''), 'Sin especificar') as tipo")
            ->selectRaw("{$mesExpr} as mes, COUNT(*) as total")
            ->whereYear('fecha_solicitud', now()->year)
            ->groupBy('tipo', 'mes')
            ->get();

        $seriesPorTipo = [];
        foreach ($porTipoMes as $fila) {
            $seriesPorTipo[$fila->tipo] = $seriesPorTipo[$fila->tipo] ?? array_fill(0, 12, 0);
            $seriesPorTipo[$fila->tipo][(int) $fila->mes - 1] = (int) $fila->total;
        }

        // ── Expedientes ─────────────────────────────────────────────────────
        $totalExpedientes = Expediente::count();
        $expedientesCompletos = Expediente::where('estado_global', 100)->count();
        $expedientesSinSolicitud = Expediente::whereNull('solicitud_id')->count();

        // ── Nómina (último período cargado) ─────────────────────────────────
        $ultimaNomina = Nomina::query()
            ->orderByDesc('periodo')
            ->orderByDesc('mes')
            ->first();

        $trabajadoresUltimaNomina = 0;
        if ($ultimaNomina) {
            $trabajadoresUltimaNomina = DB::table('nomina_trabajador as nt')
                ->join('trabajadores as t', function ($join) {
                    $join->on('t.id', '=', 'nt.trabajador_id')->whereNull('t.deleted_at');
                })
                ->where('nt.nomina_id', $ultimaNomina->id)
                ->count();
        }

        // ── Prestaciones ────────────────────────────────────────────────────
        $prestacionesCalculadas = Prestacion::count();
        $montoPrestaciones = (float) Prestacion::sum('monto');

        // ── Usuarios ────────────────────────────────────────────────────────
        $usuariosActivos = User::whereIn('rol', ['admin', 'superadmin', 'usuario', 'operador', 'analista'])->count();

        $totalSolicitudes = (int) $solicitudesPorEstado->sum();

        return [
            'trabajadores' => [
                'total' => $totalTrabajadores,
                'activos' => (int) ($porEstatusTrabajador->activos ?? 0),
                'jubilados' => (int) ($porEstatusTrabajador->jubilados ?? 0),
                'por_tipo_nomina' => [
                    'ADM' => (int) ($porTipoNomina->get('ADM') ?? 0),
                    'DOC' => (int) ($porTipoNomina->get('DOC') ?? 0),
                    'OBREROS' => (int) ($porTipoNomina->get('OBREROS') ?? 0),
                    'SIN TIPO' => (int) ($porTipoNomina->get('SIN TIPO') ?? 0),
                ],
            ],
            'solicitudes' => [
                'total' => $totalSolicitudes,
                'pendiente' => (int) ($solicitudesPorEstado->get('pendiente') ?? 0),
                'revision' => (int) ($solicitudesPorEstado->get('revision') ?? 0),
                'aprobado' => (int) ($solicitudesPorEstado->get('aprobado') ?? 0),
                'rechazado' => (int) ($solicitudesPorEstado->get('rechazado') ?? 0),
                'por_tipo' => $solicitudesPorTipo,
                'por_mes' => $serieMensual,
                'por_tipo_mes' => $seriesPorTipo,
                'tasa_aprobacion' => $totalSolicitudes > 0
                    ? round(((int) ($solicitudesPorEstado->get('aprobado') ?? 0) / $totalSolicitudes) * 100, 1)
                    : 0,
            ],
            'expedientes' => [
                'total' => $totalExpedientes,
                'completos' => $expedientesCompletos,
                'sin_solicitud' => $expedientesSinSolicitud,
                'porcentaje_completos' => $totalExpedientes > 0
                    ? round(($expedientesCompletos / $totalExpedientes) * 100, 1)
                    : 0,
            ],
            'nomina' => [
                'periodo' => $ultimaNomina?->etiqueta_periodo,
                'mes_nombre' => $ultimaNomina?->mes_nombre,
                'trabajadores' => $trabajadoresUltimaNomina,
                'total_general' => $ultimaNomina ? (float) $ultimaNomina->total_general : 0,
            ],
            'prestaciones' => [
                'calculadas' => $prestacionesCalculadas,
                'monto_total' => $montoPrestaciones,
            ],
            'usuarios_activos' => $usuariosActivos,
            'anio_actual' => now()->year,
        ];
    }
}
