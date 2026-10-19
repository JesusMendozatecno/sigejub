<?php

namespace App\Http\Controllers;

use App\Models\FormulaPrestacion;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormulaPrestacionController extends Controller
{
    public function index(Request $request)
    {
        $query = FormulaPrestacion::query();

        if ($search = $request->get('search')) {
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
        }

        if ($request->boolean('solo_activos')) {
            $query->activos();
        }

        $formulas = $query->orderBy('nombre', 'asc')
            ->paginate(min($request->get('per_page', 15), 100));

        return response()->json($formulas);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:150',
                'codigo' => 'required|string|max:50|regex:/^[A-Za-z0-9_\-]+$/',
                'descripcion' => 'nullable|string|max:1000',
                'conceptos' => 'nullable|array',
                'variables' => 'nullable|array',
                'formula_matematica' => 'nullable|string|max:2000',
                'explicacion_variables' => 'nullable|array',
                'ejemplo_calculo' => 'nullable|string|max:2000',
                'observaciones' => 'nullable|string|max:1000',
                'activo' => 'boolean',
            ]);

            $existe = DB::table('formulas_prestaciones')->where('codigo', $request->codigo)->exists();
            if ($existe) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'Ya existe una fórmula con ese código.',
                ], 422);
            }

            $id = DB::table('formulas_prestaciones')->insertGetId([
                'nombre' => $request->nombre,
                'codigo' => $request->codigo,
                'descripcion' => $request->descripcion,
                'conceptos' => $request->conceptos ? json_encode($request->conceptos) : null,
                'variables' => $request->variables ? json_encode($request->variables) : null,
                'formula_matematica' => $request->formula_matematica,
                'explicacion_variables' => $request->explicacion_variables ? json_encode($request->explicacion_variables) : null,
                'ejemplo_calculo' => $request->ejemplo_calculo,
                'observaciones' => $request->observaciones,
                'activo' => $request->boolean('activo', true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Activity::log('created', 'formula_prestacion', $id,
                "Se creó la fórmula '{$request->nombre}' ({$request->codigo})");

            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Fórmula creada exitosamente.',
                'id' => $id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['estado' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al crear fórmula: ' . $e->getMessage());
            return response()->json(['estado' => 'error', 'mensaje' => 'Error interno al crear la fórmula.'], 500);
        }
    }

    public function show(string $id)
    {
        $item = DB::table('formulas_prestaciones')->where('id', $id)->first();

        if (!$item) {
            abort(404, 'Fórmula no encontrada.');
        }

        if (is_string($item->conceptos)) {
            $item->conceptos = json_decode($item->conceptos, true);
        }
        if (is_string($item->variables)) {
            $item->variables = json_decode($item->variables, true);
        }
        if (is_string($item->explicacion_variables)) {
            $item->explicacion_variables = json_decode($item->explicacion_variables, true);
        }

        return response()->json($item);
    }

    public function update(Request $request, int $id)
    {
        try {
            $item = DB::table('formulas_prestaciones')->where('id', $id)->first();

            if (!$item) {
                abort(404, 'Fórmula no encontrada.');
            }

            $request->validate([
                'nombre' => 'required|string|max:150',
                'codigo' => 'required|string|max:50|regex:/^[A-Za-z0-9_\-]+$/',
                'descripcion' => 'nullable|string|max:1000',
                'conceptos' => 'nullable|array',
                'variables' => 'nullable|array',
                'formula_matematica' => 'nullable|string|max:2000',
                'explicacion_variables' => 'nullable|array',
                'ejemplo_calculo' => 'nullable|string|max:2000',
                'observaciones' => 'nullable|string|max:1000',
                'activo' => 'boolean',
            ]);

            $existe = DB::table('formulas_prestaciones')
                ->where('codigo', $request->codigo)
                ->where('id', '!=', $id)
                ->exists();
            if ($existe) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'Ya existe otra fórmula con ese código.',
                ], 422);
            }

            DB::table('formulas_prestaciones')->where('id', $id)->update([
                'nombre' => $request->nombre,
                'codigo' => $request->codigo,
                'descripcion' => $request->descripcion,
                'conceptos' => $request->conceptos ? json_encode($request->conceptos) : $item->conceptos,
                'variables' => $request->variables ? json_encode($request->variables) : $item->variables,
                'formula_matematica' => $request->formula_matematica,
                'explicacion_variables' => $request->explicacion_variables ? json_encode($request->explicacion_variables) : $item->explicacion_variables,
                'ejemplo_calculo' => $request->ejemplo_calculo,
                'observaciones' => $request->observaciones,
                'activo' => $request->boolean('activo', $item->activo),
                'updated_at' => now(),
            ]);

            Activity::log('updated', 'formula_prestacion', $id,
                "Se actualizó la fórmula '{$request->nombre}' ({$request->codigo})");

            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Fórmula actualizada exitosamente.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['estado' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al actualizar fórmula: ' . $e->getMessage());
            return response()->json(['estado' => 'error', 'mensaje' => 'Error interno al actualizar la fórmula.'], 500);
        }
    }

    public function destroy(string $id)
    {
        $item = DB::table('formulas_prestaciones')->where('id', $id)->first();

        if (!$item) {
            abort(404, 'Fórmula no encontrada.');
        }

        DB::table('formulas_prestaciones')->where('id', $id)->delete();

        Activity::log('deleted', 'formula_prestacion', $id,
            "Se eliminó la fórmula '{$item->nombre}'");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Fórmula eliminada exitosamente.',
        ]);
    }
}
