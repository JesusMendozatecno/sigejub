<?php
// Controlador de expedientes de jubilación.
// Gestiona la creación de expedientes, vinculación con trabajador y solicitud,
// carga y revisión de documentos, y cálculo automático del estado global (%).

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\Trabajador;
use App\Models\Solicitud;
use App\Models\Activity;
use Illuminate\Http\Request;

class ExpedienteController extends Controller
{
    public function index()
    {
        $expedientes = Expediente::with('trabajador', 'solicitud', 'documentos')
            ->latest()
            ->get();
        return response()->json($expedientes);
    }

    public function buscarTrabajador(Request $request)
    {
        $cedula = $request->get('cedula');
        $trabajador = Trabajador::where('cedula', $cedula)->first();

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

        if ($request->hasFile('foto_carnet')) {
            $validated['foto_carnet'] = $request->file('foto_carnet')->store('expedientes/fotos', 'public');
        }

        $validated['estado_global'] = 0;

        $expediente = Expediente::create($validated);

        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                $path = $file->store('expedientes/documentos', 'public');
                $expediente->documentos()->create([
                    'nombre' => $file->getClientOriginalName(),
                    'archivo' => $path,
                    'estado' => 'en_revision',
                ]);
            }
        }

        $trabajador = $expediente->trabajador;
        Activity::log('created', 'expediente', $expediente->id,
            "Se creó el expediente de {$trabajador->nombres} {$trabajador->apellidos}");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Expediente creado exitosamente.',
            'expediente' => $expediente->load('trabajador', 'documentos'),
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

        $documento->update($validated);

        $this->recalcularEstadoGlobal($documento->expediente_id);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Estado del documento actualizado.',
        ]);
    }

    public function subirDocumento(Request $request, $id)
    {
        $expediente = Expediente::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
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
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
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
        $expediente = Expediente::with('documentos')->findOrFail($expedienteId);
        $total = $expediente->documentos->count();
        if ($total === 0) {
            $expediente->update(['estado_global' => 0]);
            return;
        }
        $aprobados = $expediente->documentos->where('estado', 'aprobado')->count();
        $porcentaje = round(($aprobados / $total) * 100);
        $expediente->update(['estado_global' => $porcentaje]);
    }
}
