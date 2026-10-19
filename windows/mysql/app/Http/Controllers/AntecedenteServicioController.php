<?php
// Controlador de antecedentes de servicio del trabajador.
// Registra los períodos laborales (dentro y fuera de la institución) con su
// cargo, condición laboral y dedicación, además de los documentos de respaldo
// (providencias, oficios, constancias y folios) de cada antecedente.

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AntecedenteDocumento;
use App\Models\AntecedenteServicio;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AntecedenteServicioController extends Controller
{
    public const TIPOS_DOCUMENTO = [
        'Constancia de Trabajo',
        'Oficio',
        'Providencia',
        'Resolución',
        'Nombramiento',
        'Otro',
    ];

    /**
     * LISTAR antecedentes de un trabajador (junto a su ficha resumida)
     */
    public function index($trabajadorId)
    {
        $trabajador = Trabajador::findOrFail($trabajadorId);

        $condicion = $trabajador->tipo_contrato_id
            ? optional(DB::table('tipos_contrato')->where('id', $trabajador->tipo_contrato_id)->first())->nombre
            : null;

        $antecedentes = $trabajador->antecedentesServicio()
            ->with('documentos')
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return response()->json([
            'trabajador' => [
                'id' => $trabajador->id,
                'nombre_completo' => $trabajador->nombres . ' ' . $trabajador->apellidos,
                'cedula' => $trabajador->cedula,
                'cargo' => $trabajador->cargo,
                'unidad_departamento' => $trabajador->unidad_departamento,
                'condicion_laboral' => $condicion,
                'dedicacion' => $trabajador->dedicacion,
                'fecha_ingreso' => $trabajador->fecha_ingreso,
            ],
            'tipos_documento' => self::TIPOS_DOCUMENTO,
            'condiciones' => DB::table('tipos_contrato')->where('activo', 1)->orderBy('nombre')->pluck('nombre'),
            'dedicaciones' => DB::table('dedicaciones')->orderBy('nombre')->pluck('nombre'),
            'antecedentes' => $antecedentes,
        ]);
    }

    /**
     * REGISTRAR un período de servicio
     */
    public function store(Request $request, $trabajadorId)
    {
        $trabajador = Trabajador::findOrFail($trabajadorId);

        $validated = $request->validate([
            'institucion' => 'required|string|max:150',
            'es_institucion_actual' => 'nullable|boolean',
            'cargo' => 'nullable|string|max:150',
            'condicion_laboral' => 'nullable|string|max:100',
            'dedicacion' => 'nullable|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string|max:2000',
        ]);

        $antecedente = $trabajador->antecedentesServicio()->create($validated);

        $this->sincronizarEstadoActual($trabajador, $antecedente);

        Activity::log('created', 'antecedente_servicio', $antecedente->id,
            "Se registró antecedente de servicio de {$trabajador->nombres} {$trabajador->apellidos} ({$antecedente->institucion})");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Antecedente de servicio registrado correctamente.',
            'antecedente' => $antecedente->load('documentos'),
        ]);
    }

    /**
     * MODIFICAR un período de servicio
     */
    public function update(Request $request, $id)
    {
        $antecedente = AntecedenteServicio::with('trabajador')->findOrFail($id);

        $validated = $request->validate([
            'institucion' => 'required|string|max:150',
            'es_institucion_actual' => 'nullable|boolean',
            'cargo' => 'nullable|string|max:150',
            'condicion_laboral' => 'nullable|string|max:100',
            'dedicacion' => 'nullable|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string|max:2000',
        ]);

        $antecedente->update($validated);

        if ($antecedente->trabajador) {
            $this->sincronizarEstadoActual($antecedente->trabajador, $antecedente);
        }

        Activity::log('updated', 'antecedente_servicio', $antecedente->id,
            "Se actualizó un antecedente de servicio ({$antecedente->institucion})");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Antecedente actualizado correctamente.',
            'antecedente' => $antecedente->load('documentos'),
        ]);
    }

    /**
     * ELIMINAR un período de servicio (y sus documentos)
     */
    public function destroy($id)
    {
        $antecedente = AntecedenteServicio::with('documentos')->findOrFail($id);

        foreach ($antecedente->documentos as $documento) {
            if ($documento->archivo) {
                Storage::disk('public')->delete($documento->archivo);
            }
        }
        $antecedente->delete();

        Activity::log('deleted', 'antecedente_servicio', $antecedente->id,
            "Se eliminó un antecedente de servicio ({$antecedente->institucion})");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Antecedente eliminado correctamente.',
        ]);
    }

    /**
     * ADJUNTAR un documento de respaldo a un antecedente (R7)
     */
    public function subirDocumento(Request $request, $id)
    {
        $antecedente = AntecedenteServicio::findOrFail($id);

        $validated = $request->validate([
            'tipo_documento' => 'required|string|max:100|in:' . implode(',', self::TIPOS_DOCUMENTO),
            'numero_providencia' => 'nullable|string|max:50',
            'fecha_emision' => 'nullable|date',
            'institucion_emisora' => 'nullable|string|max:150',
            'folio_numero' => 'nullable|string|max:50',
            'folio_hojas' => 'nullable|integer|min:1|max:9999',
            'observaciones' => 'nullable|string|max:2000',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
        ]);

        if ($request->hasFile('archivo')) {
            $validated['archivo'] = $request->file('archivo')->store('antecedentes/documentos', 'public');
        } else {
            unset($validated['archivo']);
        }

        $documento = $antecedente->documentos()->create($validated);

        Activity::log('created', 'antecedente_documento', $documento->id,
            "Se adjuntó \"{$documento->tipo_documento}\" a un antecedente de servicio");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Documento de respaldo adjuntado correctamente.',
            'documento' => $documento,
        ]);
    }

    /**
     * ELIMINAR un documento de respaldo
     */
    public function eliminarDocumento($id)
    {
        $documento = AntecedenteDocumento::findOrFail($id);

        if ($documento->archivo) {
            Storage::disk('public')->delete($documento->archivo);
        }
        $documento->delete();

        Activity::log('deleted', 'antecedente_documento', $documento->id,
            "Se eliminó un documento de respaldo de antecedente de servicio");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Documento eliminado correctamente.',
        ]);
    }

    /**
     * Al guardar el período vigente en la institución, refleja la condición
     * laboral y la dedicación en la ficha actual del trabajador.
     */
    private function sincronizarEstadoActual(Trabajador $trabajador, AntecedenteServicio $antecedente): void
    {
        if (!$antecedente->es_institucion_actual || $antecedente->fecha_fin) {
            return;
        }

        $datos = [];

        if ($antecedente->condicion_laboral) {
            $condicionId = DB::table('tipos_contrato')
                ->where('nombre', $antecedente->condicion_laboral)
                ->value('id');
            if ($condicionId) {
                $datos['tipo_contrato_id'] = $condicionId;
            }
        }

        if ($antecedente->dedicacion !== null && $antecedente->dedicacion !== '') {
            $datos['dedicacion'] = $antecedente->dedicacion;
        }

        if ($datos) {
            $trabajador->update($datos);
        }
    }
}
