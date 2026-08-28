<?php
// Controlador genérico para CRUD de tablas maestras del sistema SIGEJUB.
// Maneja de forma genérica: Cargos, Áreas, Grados, Niveles de Instrucción,
// Tipos de Contrato, Sueldos, Primas y Tipos de Jubilación.

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MasterDataController extends Controller
{
    private array $models = [
        'cargo' => ['table' => 'cargos', 'label' => 'Cargo'],
        'area' => ['table' => 'areas', 'label' => 'Área'],
        'grado' => ['table' => 'grados', 'label' => 'Grado'],
        'nivel-instruccion' => ['table' => 'niveles_instruccion', 'label' => 'Nivel de Instrucción'],
        'tipo-contrato' => ['table' => 'tipos_contrato', 'label' => 'Tipo de Contrato'],
        'prima' => ['table' => 'primas', 'label' => 'Prima', 'extra_fields' => ['monto', 'valor', 'fecha_vigencia']],
        'tipo-jubilacion' => ['table' => 'tipos_jubilacion', 'label' => 'Tipo de Jubilación'],
    ];

    private function resolveConfig(string $tipo): array
    {
        if (!isset($this->models[$tipo])) {
            abort(404, 'Tipo de tabla maestra no válido.');
        }
        return $this->models[$tipo];
    }

    public function index(Request $request, string $tipo)
    {
        $config = $this->resolveConfig($tipo);
        $query = DB::table($config['table']);

        if ($search = $request->get('search')) {
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
        }

        if ($request->boolean('solo_activos')) {
            $query->where('activo', true);
        }

        $items = $query->orderBy('nombre', 'asc')
                       ->paginate(min($request->get('per_page', 15), 100));

        return response()->json($items);
    }

    public function store(Request $request, string $tipo)
    {
        $config = $this->resolveConfig($tipo);

        $rules = [
            'nombre' => 'required|string|max:150',
            'codigo' => 'required|string|max:50|unique:' . $config['table'] . ',codigo|regex:/^[A-Za-z0-9_\-]+$/',
            'activo' => 'boolean',
        ];

        if (in_array('monto', $config['extra_fields'] ?? [])) {
            $rules['monto'] = 'nullable|numeric|min:0';
        }
        if (in_array('valor', $config['extra_fields'] ?? [])) {
            $rules['valor'] = 'nullable|numeric|min:0';
        }
        if (in_array('fecha_vigencia', $config['extra_fields'] ?? [])) {
            $rules['fecha_vigencia'] = 'nullable|date';
        }

        $request->validate($rules);

        $data = $request->only(array_keys($rules));
        $data['activo'] = $data['activo'] ?? true;

        $id = DB::table($config['table'])->insertGetId($data);

        Activity::log('created', $config['table'], $id,
            "Se creó {$config['label']} '{$data['nombre']}'");

        return response()->json([
            'estado' => 'success',
            'mensaje' => "{$config['label']} creado exitosamente.",
            'id' => $id,
        ]);
    }

    public function show(string $tipo, int $id)
    {
        $config = $this->resolveConfig($tipo);
        $item = DB::table($config['table'])->where('id', $id)->first();

        if (!$item) {
            abort(404, "{$config['label']} no encontrado.");
        }

        return response()->json($item);
    }

    public function update(Request $request, string $tipo, int $id)
    {
        $config = $this->resolveConfig($tipo);
        $item = DB::table($config['table'])->where('id', $id)->first();

        if (!$item) {
            abort(404, "{$config['label']} no encontrado.");
        }

        $rules = [
            'nombre' => 'required|string|max:150',
            'codigo' => 'required|string|max:50|unique:' . $config['table'] . ',codigo,' . $id . '|regex:/^[A-Za-z0-9_\-]+$/',
            'activo' => 'boolean',
        ];

        if (in_array('monto', $config['extra_fields'] ?? [])) {
            $rules['monto'] = 'nullable|numeric|min:0';
        }
        if (in_array('valor', $config['extra_fields'] ?? [])) {
            $rules['valor'] = 'nullable|numeric|min:0';
        }
        if (in_array('fecha_vigencia', $config['extra_fields'] ?? [])) {
            $rules['fecha_vigencia'] = 'nullable|date';
        }

        $request->validate($rules);

        $data = $request->only(array_keys($rules));
        DB::table($config['table'])->where('id', $id)->update($data);

        Activity::log('updated', $config['table'], $id,
            "Se actualizó {$config['label']} '{$data['nombre']}'");

        return response()->json([
            'estado' => 'success',
            'mensaje' => "{$config['label']} actualizado exitosamente.",
        ]);
    }

    public function destroy(string $tipo, int $id)
    {
        $config = $this->resolveConfig($tipo);
        $item = DB::table($config['table'])->where('id', $id)->first();

        if (!$item) {
            abort(404, "{$config['label']} no encontrado.");
        }

        DB::table($config['table'])->where('id', $id)->delete();

        Activity::log('deleted', $config['table'], $id,
            "Se eliminó {$config['label']} '{$item->nombre}'");

        return response()->json([
            'estado' => 'success',
            'mensaje' => "{$config['label']} eliminado exitosamente.",
        ]);
    }
}
