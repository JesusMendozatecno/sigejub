<?php
// Controlador del catálogo de cláusulas de la convención colectiva.
// Permite listar las cláusulas activas (para asignarlas en el trabajador y en el
// expediente) y administrarlas desde Cargos y Grados (admin/superadmin).

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ClausulaConvencion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClausulaConvencionController extends Controller
{
    public function index(Request $request)
    {
        $query = ClausulaConvencion::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%");
            });
        }

        if ($request->filled('solo_activos') && (string) $request->get('solo_activos') !== '') {
            $query->where('activo', $request->boolean('solo_activos'));
        }

        $clausulas = $query->orderBy('numero')->paginate(min((int) $request->get('per_page', 100), 200));

        return response()->json($clausulas);
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);

        $clausula = ClausulaConvencion::create($validated);

        Activity::log('created', 'clausula_convencion', $clausula->id,
            "Se registró la cláusula {$clausula->numero} de la convención colectiva");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Cláusula registrada correctamente.',
            'clausula' => $clausula,
        ]);
    }

    public function update(Request $request, $id)
    {
        $clausula = ClausulaConvencion::findOrFail($id);
        $clausula->update($this->validar($request, $clausula->id));

        Activity::log('updated', 'clausula_convencion', $clausula->id,
            "Se actualizó la cláusula {$clausula->numero} de la convención colectiva");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Cláusula actualizada correctamente.',
            'clausula' => $clausula,
        ]);
    }

    public function destroy($id)
    {
        $clausula = ClausulaConvencion::findOrFail($id);
        $numero = $clausula->numero;
        $clausula->delete();

        Activity::log('deleted', 'clausula_convencion', (int) $id,
            "Se eliminó la cláusula {$numero} de la convención colectiva");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Cláusula eliminada correctamente.',
        ]);
    }

    private function validar(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'numero' => [
                'required', 'string', 'max:30',
                Rule::unique('clausulas_convencion', 'numero')->ignore($id),
            ],
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:2000',
            'tipo_calculo' => 'required|in:' . implode(',', array_keys(ClausulaConvencion::TIPOS)),
            'valor' => 'nullable|numeric|min:0|max:9999999999',
            'por_anio' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]) + [
            'valor' => (float) $request->input('valor', 0),
            'por_anio' => $request->boolean('por_anio'),
            'activo' => $request->boolean('activo', true),
        ];
    }
}
