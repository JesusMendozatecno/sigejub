<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;
use App\Models\Trabajador;
use Barryvdh\DomPDF\Facade\Pdf;

class SolicitudController extends Controller
{
    public function index(Request $request)
    {
        $query = Solicitud::with('trabajador');

        if ($estado = $request->get('estado')) {
            if ($estado === 'pending') {
                $query->where('estado', 'pendiente');
            } elseif ($estado === 'approved') {
                $query->where('estado', 'aprobado');
            } elseif ($estado === 'rejected') {
                $query->where('estado', 'rechazado');
            }
        }

        if ($search = $request->get('search')) {
            $query->whereHas('trabajador', function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        $solicitudes = $query->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 10));

        return response()->json($solicitudes);
    }

    public function exportarPDF(Request $request)
    {
        $query = Solicitud::with('trabajador');

        if ($estado = $request->get('estado')) {
            if ($estado === 'pending') {
                $query->where('estado', 'pendiente');
            } elseif ($estado === 'approved') {
                $query->where('estado', 'aprobado');
            } elseif ($estado === 'rejected') {
                $query->where('estado', 'rechazado');
            }
        }

        $solicitudes = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.solicitudes', compact('solicitudes'));
        return $pdf->download('solicitudes-' . now()->format('Y-m-d') . '.pdf');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'trabajador_id' => 'required|exists:trabajadores,id',
                'fecha_solicitud' => 'required|date',
                'periodo' => 'nullable|string|max:20',
                'tipo_jubilacion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',
            ]);

            $validated['estado'] = 'pendiente';

            Solicitud::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Solicitud de jubilación registrada exitosamente.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $solicitud = Solicitud::with('trabajador')->findOrFail($id);
        return response()->json($solicitud);
    }

    public function update(Request $request, $id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);

            $rules = [
                'trabajador_id' => 'sometimes|required|exists:trabajadores,id',
                'fecha_solicitud' => 'sometimes|required|date',
                'periodo' => 'nullable|string|max:20',
                'tipo_jubilacion' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string',
                'estado' => 'nullable|in:pendiente,revision,aprobado,rechazado',
            ];

            $validated = $request->validate($rules);

            $solicitud->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Solicitud actualizada correctamente.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);
            $solicitud->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Solicitud eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
