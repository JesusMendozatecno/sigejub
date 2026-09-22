<?php
// Controlador de la Caja Negra (auditoría completa del sistema).
// Proporciona acceso detallado a todas las actividades con filtros avanzados,
// estadísticas agregadas y exportación (PDF y CSV). Solo accesible por administradores.
// La Caja Negra es de SOLO LECTURA: no expone edición ni eliminación de registros.

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CajaNegraController extends Controller
{
    // Listado con filtros: usuario, acción, tipo de entidad, búsqueda, rango de fechas.
    public function index(Request $request)
    {
        $query = Activity::with('user');

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($accion = $request->get('accion')) {
            $query->where('accion', $accion);
        }

        if ($tipoEntidad = $request->get('tipo_entidad')) {
            $query->where('tipo_entidad', $tipoEntidad);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('direccion_ip', 'like', "%{$search}%")
                  ->orWhere('ruta', 'like', "%{$search}%");
            });
        }

        if ($from = $request->get('from')) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }

        if ($to = $request->get('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $activities = $query->latest()->paginate(min((int) $request->get('per_page', 50), 100));

        // Enriquecer con etiquetas traducidas y rol del usuario.
        $data = $activities->getCollection()->map(function (Activity $a) {
            return $this->serializar($a);
        });
        $activities->setCollection($data);

        return response()->json($activities);
    }

    // Detalle de un registro de auditoría.
    public function show($id)
    {
        $activity = Activity::with('user')->findOrFail($id);
        return response()->json($this->serializar($activity));
    }

    // Estadísticas agregadas (inmutables, solo lectura).
    public function stats()
    {
        $total = Activity::count();
        $today = Activity::whereDate('created_at', today())->count();
        $byAction = Activity::selectRaw('accion, COUNT(*) as total')
            ->groupBy('accion')->orderByDesc('total')->get()
            ->map(function ($row) {
                $row->label = AuditService::accionHumana($row->accion);
                return $row;
            });
        $byType = Activity::selectRaw('tipo_entidad, COUNT(*) as total')
            ->groupBy('tipo_entidad')->orderByDesc('total')->get()
            ->map(function ($row) {
                $row->label = AuditService::entidadHumana($row->tipo_entidad);
                return $row;
            });
        $byUser = Activity::selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')->orderByDesc('total')->take(10)->with('user')->get();
        $lastWeek = Activity::where('created_at', '>=', now()->subDays(7))->count();

        $labelAcciones = array_values(AuditService::ACCION_ETIQUETAS);
        $labelEntidades = array_values(AuditService::ENTIDAD_ETIQUETAS);

        return response()->json(compact(
            'total', 'today', 'byAction', 'byType', 'byUser', 'lastWeek',
            'labelAcciones', 'labelEntidades'
        ));
    }

    // Exportación respetando filtros (PDF).
    public function exportar(Request $request)
    {
        $query = Activity::with('user');

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($accion = $request->get('accion')) {
            $query->where('accion', $accion);
        }
        if ($tipoEntidad = $request->get('tipo_entidad')) {
            $query->where('tipo_entidad', $tipoEntidad);
        }
        if ($search = $request->get('search')) {
            $query->where('descripcion', 'like', "%{$search}%");
        }
        if ($from = $request->get('from')) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to = $request->get('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $activities = $query->latest()->limit(5000)->get();

        // Registrar la exportación en Caja Negra.
        AuditService::registrar(
            'exported',
            'activity',
            null,
            "Exportación del historial (PDF) con " . $activities->count() . " registros",
            null,
            ['registros' => $activities->count(), 'filtros' => AuditService::sanitizar($request->except(['_token']))],
            AuditService::sanitizar($request->except(['_token']))
        );

        return view('pdf.caja-negra', compact('activities'));
    }

    // Exportación CSV respetando filtros.
    public function exportarCsv(Request $request)
    {
        $query = Activity::with('user');

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($accion = $request->get('accion')) {
            $query->where('accion', $accion);
        }
        if ($tipoEntidad = $request->get('tipo_entidad')) {
            $query->where('tipo_entidad', $tipoEntidad);
        }
        if ($search = $request->get('search')) {
            $query->where('descripcion', 'like', "%{$search}%");
        }
        if ($from = $request->get('from')) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to = $request->get('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $activities = $query->latest()->limit(5000)->get();

        $filename = 'caja_negra_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel
        fputcsv($handle, ['Fecha', 'Usuario', 'Rol', 'Acción', 'Tipo', 'ID', 'Descripción', 'IP', 'Método', 'Ruta']);

        foreach ($activities as $a) {
            fputcsv($handle, [
                $a->created_at?->format('d/m/Y H:i:s'),
                $a->user ? ($a->user->nombre . ' ' . $a->user->apellido) : 'Sistema',
                $a->user?->rol ?? '—',
                AuditService::accionHumana($a->accion),
                AuditService::entidadHumana($a->tipo_entidad),
                $a->entidad_id ?? '—',
                $a->descripcion,
                $a->direccion_ip ?? '—',
                $a->metodo ?? '—',
                $a->ruta ?? '—',
            ]);
        }
        rewind($handle);
        $contenido = stream_get_contents($handle);
        fclose($handle);

        AuditService::registrar(
            'exported',
            'activity',
            null,
            "Exportación del historial (CSV) con " . $activities->count() . " registros",
            null,
            ['registros' => $activities->count()],
            AuditService::sanitizar($request->except(['_token']))
        );

        return response($contenido, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Lista de usuarios para el filtro.
    public function usuarios()
    {
        return response()->json(User::select('id', 'nombre', 'apellido', 'correo', 'rol')->orderBy('nombre')->get());
    }

    // Serializa un registro para la UI con etiquetas en español y datos técnicos.
    private function serializar(Activity $a): array
    {
        return [
            'id'                  => $a->id,
            'user_id'             => $a->user_id,
            'accion'              => $a->accion,
            'accion_humana'       => AuditService::accionHumana($a->accion),
            'tipo_entidad'        => $a->tipo_entidad,
            'tipo_entidad_humana' => AuditService::entidadHumana($a->tipo_entidad),
            'entidad_id'          => $a->entidad_id,
            'descripcion'         => $a->descripcion,
            'direccion_ip'        => $a->direccion_ip,
            'navegador'           => $a->navegador,
            'metodo'              => $a->metodo,
            'ruta'                => $a->ruta,
            'valores_anteriores'  => $a->valores_anteriores,
            'valores_nuevos'      => $a->valores_nuevos,
            'datos_peticion'      => $a->datos_peticion,
            'created_at'          => $a->created_at?->toISOString(),
            'usuario_nombre'      => $a->user ? trim($a->user->nombre . ' ' . ($a->user->apellido ?? '')) : 'Sistema',
            'usuario_correo'      => $a->user?->correo,
            'usuario_rol'         => $a->user?->rol,
        ];
    }
}