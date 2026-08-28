<?php
// Controlador de expedientes de jubilación.
// Gestiona la creación de expedientes, vinculación con trabajador y solicitud,
// carga y revisión de documentos, y cálculo automático del estado global (%).

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\Trabajador;
use App\Models\Solicitud;
use App\Models\Activity;
use App\Services\NotificationService;
use App\Services\ValidationService;
use Illuminate\Http\Request;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 10), 100);
        $expedientes = Expediente::with('trabajador', 'solicitud', 'documentos')
            ->latest()
            ->paginate($perPage);
        return response()->json($expedientes);
    }

    public function buscarTrabajador(Request $request)
    {
        $cedula = $request->get('cedula');
        $cedula = trim($cedula);

        // Buscar coincidencia exacta primero
        $trabajador = Trabajador::where('cedula', $cedula)->first();

        // Si no encontró, intentar sin el prefijo (V-, E-, etc.)
        if (!$trabajador) {
            $trabajador = Trabajador::where('cedula', 'like', "%{$cedula}%")->first();
        }

        if (!$trabajador) {
            return response()->json(['error' => 'Trabajador no encontrado'], 404);
        }

        $solicitud = Solicitud::where('trabajador_id', $trabajador->id)->first();
        if (!$solicitud) {
            return response()->json(['error' => 'El trabajador no tiene ninguna solicitud registrada'], 404);
        }

        $yaTieneExpediente = Expediente::where('trabajador_id', $trabajador->id)->exists();
        if ($yaTieneExpediente) {
            return response()->json(['error' => 'Este trabajador ya tiene un expediente creado'], 409);
        }

        return response()->json([
            'trabajador' => $trabajador,
            'solicitud' => $solicitud,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'trabajador_id' => 'required|exists:trabajadores,id',
            'solicitud_id' => 'required|exists:solicitudes,id',
            'foto_carnet' => 'nullable|image|max:2048',
        ]);

        // Validación de negocio: solicitud debe estar aprobada
        $solicitud = Solicitud::findOrFail($validated['solicitud_id']);
        if ($solicitud->estado !== 'aprobado') {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Solo se puede crear expediente con solicitud aprobada.',
            ], 422);
        }

        // Validación de negocio: trabajador no debe tener expediente
        if (!ValidationService::trabajadorSinExpediente($validated['trabajador_id'])) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Este trabajador ya tiene un expediente creado.',
            ], 422);
        }

        if ($request->hasFile('foto_carnet')) {
            $validated['foto_carnet'] = $request->file('foto_carnet')->store('expedientes/fotos', 'public');
        }

        $validated['estado_global'] = 0;

        $expediente = Expediente::create($validated);

        $trabajador = $expediente->trabajador;
        Activity::log('created', 'expediente', $expediente->id,
            "Se creó el expediente de {$trabajador->nombres} {$trabajador->apellidos}");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Expediente creado exitosamente.',
            'expediente' => $expediente->load('trabajador'),
        ]);
    }

    public function show($id)
    {
        $expediente = Expediente::with('trabajador', 'solicitud', 'documentos')->findOrFail($id);
        return response()->json($expediente);
    }

    public function updateDocumentoStatus(Request $request, $id)
    {
        $documento = \App\Models\Documento::findOrFail($id);
        $validated = $request->validate([
            'estado' => 'required|in:en_revision,aprobado,rechazado',
            'nota_rechazo' => 'nullable|string',
        ]);

        $previo = $documento->estado;

        if ($validated['estado'] === 'rechazado') {
            $expediente = Expediente::with('trabajador')->find($documento->expediente_id);
            if ($expediente && $expediente->trabajador) {
                $nombre = "{$expediente->trabajador->nombres} {$expediente->trabajador->apellidos}";
                NotificationService::documentoRechazado(
                    $expediente->id,
                    $documento->nombre,
                    $nombre
                );
            }
            $expedienteId = $documento->expediente_id;
            $documento->delete();
            $this->recalcularEstadoGlobal($expedienteId);
            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Documento rechazado y eliminado.',
            ]);
        }

        $documento->update($validated);
        $this->recalcularEstadoGlobal($documento->expediente_id);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Estado del documento actualizado.',
        ]);
    }

    public function updateFotoCarnet(Request $request, $id)
    {
        $expediente = Expediente::findOrFail($id);

        $request->validate([
            'foto_carnet' => 'required|image|max:2048',
        ]);

        $path = $request->file('foto_carnet')->store('expedientes/fotos', 'public');
        $expediente->update(['foto_carnet' => $path]);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Foto carnet actualizada.',
            'foto_carnet' => $path,
        ]);
    }

    public function subirDocumento(Request $request, $id)
    {
        $expediente = Expediente::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $path = $request->file('archivo')->store('expedientes/documentos', 'public');

        $expediente->documentos()->create([
            'nombre' => $validated['nombre'],
            'archivo' => $path,
            'estado' => 'en_revision',
        ]);

        $this->recalcularEstadoGlobal($expediente->id);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Documento subido correctamente.',
        ]);
    }

    public function reemplazarDocumento(Request $request, $id)
    {
        $documento = \App\Models\Documento::findOrFail($id);

        $validated = $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $path = $request->file('archivo')->store('expedientes/documentos', 'public');
        $documento->update([
            'archivo' => $path,
            'estado' => 'en_revision',
            'nota_rechazo' => null,
        ]);

        $this->recalcularEstadoGlobal($documento->expediente_id);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Documento reemplazado correctamente.',
        ]);
    }

    public function listosParaAprobacion()
    {
        abort_unless(in_array(auth()->user()?->rol, ['admin', 'superadmin']), 403);

        $expedientes = Expediente::with('trabajador', 'solicitud', 'documentos')
            ->where('estado_global', 100)
            ->whereNull('carta_aprobacion')
            ->latest()
            ->take(20)
            ->get();

        return response()->json($expedientes);
    }

    public function subirCartaAprobacion(Request $request, $id)
    {
        abort_unless(auth()->user()?->rol === 'superadmin', 403);

        $expediente = Expediente::findOrFail($id);

        $request->validate([
            'carta' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $path = $request->file('carta')->store('expedientes/cartas', 'public');
        $expediente->update(['carta_aprobacion' => $path]);

        $trabajador = $expediente->trabajador;
        $nombre = "{$trabajador->nombres} {$trabajador->apellidos}";
        Activity::log('updated', 'expediente', $expediente->id,
            "Se subió la carta de aprobación del consejo para {$nombre}");

        NotificationService::cartaAprobacionSubida($expediente->id, $nombre);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Carta de aprobación subida correctamente.',
        ]);
    }

    public function updateNotas(Request $request, $id)
    {
        $expediente = Expediente::findOrFail($id);
        $validated = $request->validate([
            'notas_admin' => 'nullable|string',
        ]);

        $expediente->update($validated);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Notas actualizadas.',
        ]);
    }

    private function recalcularEstadoGlobal($expedienteId)
    {
        $expediente = Expediente::with('documentos', 'trabajador')->findOrFail($expedienteId);
        $total = $expediente->documentos->count();
        if ($total === 0) {
            $expediente->update(['estado_global' => 0]);
            return;
        }
        $aprobados = $expediente->documentos->where('estado', 'aprobado')->count();
        $porcentaje = round(($aprobados / $total) * 100);

        $previo = $expediente->estado_global;
        $expediente->update(['estado_global' => $porcentaje]);

        // Notificar cuando el expediente alcanza 100% por primera vez
        if ($previo < 100 && $porcentaje === 100) {
            $t = $expediente->trabajador;
            $nombre = $t ? "{$t->nombres} {$t->apellidos}" : "ID {$expediente->trabajador_id}";
            NotificationService::expedienteListo($expediente->id, $nombre);
        }
    }
}
