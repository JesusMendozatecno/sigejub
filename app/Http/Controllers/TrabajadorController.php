<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trabajador;
use Carbon\Carbon;

class TrabajadorController extends Controller
{
 public function store(Request $request)
{
    try {
        // 1. Validamos los datos que vienen del formulario
                $validated = $request->validate([
                'cedula' => 'required|unique:trabajadores,cedula',
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'genero' => 'required|in:M,F',
                'cargo' => 'required|string',
                'unidad_departamento' => 'required|string',
                'grado_nivel' => 'required|string|max:50',

                'fecha_ingreso' => 'required|date',
                'fecha_nacimiento' => 'required|date',

                'anos_servicio_externo' => 'nullable|integer',
                'nivel_instruccion' => 'nullable|integer',

                'cuenta_bancaria' => 'nullable|string|max:20',

                'numero_hijos' => 'nullable|integer',
                'hijos_discapacidad' => 'nullable|integer',

                'porcentaje_antiguedad' => 'nullable|numeric',
                'porcentaje_caja_ahorro' => 'nullable|numeric',
            ]);

        // 2. Preparamos el array completo para la DB
        $datos = $validated;

        // --- CÁLCULOS AUTOMÁTICOS ---
        // Edad
        $datos['edad'] = \Carbon\Carbon::parse($request->fecha_nacimiento)->age;

        // Años en la institución (inst)
        $datos['anos_servicio_inst'] = \Carbon\Carbon::parse($request->fecha_ingreso)->diffInYears(now());

        // Total años (Inst + Externo)
        $datos['total_anos_servicio'] = $datos['anos_servicio_inst'] + ($request->anos_servicio_externo ?? 0);

        // 3. Creamos el registro usando $datos (que ya tiene los cálculos)
        \App\Models\Trabajador::create($datos);

        return response()->json([
            'status' => 'success',
            'message' => 'Trabajador registrado exitosamente en Sigejub.'
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ], 500);
    }
}

}