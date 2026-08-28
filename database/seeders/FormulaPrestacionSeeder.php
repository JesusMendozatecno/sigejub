<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormulaPrestacionSeeder extends Seeder
{
    public function run(): void
    {
        $formulas = [
            [
                'nombre' => 'Prestación de Antigüedad',
                'codigo' => 'PRESTACION_ANTIGUEDAD',
                'descripcion' => 'Cálculo de la prestación por antigüedad del trabajador según la legislación laboral venezolana vigente.',
                'conceptos' => json_encode([
                    'Sueldo Base',
                    'Primas salariales',
                    'Sueldo Integral',
                    'Porcentaje de jubilación',
                    'Años de servicio',
                ]),
                'variables' => json_encode([
                    'sueldo_base' => 'Salario base mensual del trabajador en bolívares',
                    'total_primas' => 'Suma de todas las primas salariales aplicables',
                    'porcentaje_jubilacion' => 'Porcentaje aplicable: 100% jubilación o 82.5% incapacidad',
                    'anios_servicio' => 'Total de años de servicio del trabajador',
                ]),
                'formula_matematica' => 'Monto = (Sueldo Base + Total Primas) × (Porcentaje Jubilación / 100) × Años de Servicio',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Sueldo Base', 'explicacion' => 'Salario mensual nominal del trabajador. Se ingresa manualmente o se obtiene del tabulador de sueldos.'],
                    ['variable' => 'Total Primas', 'explicacion' => 'Suma de todas las primas: familiar, hijos, profesionalización, responsabilidad, cesta ticket, actividad universitaria, etc.'],
                    ['variable' => 'Porcentaje Jubilación', 'explicacion' => '100% para jubilación ordinaria, 82.5% para casos de incapacidad.'],
                    ['variable' => 'Años de Servicio', 'explicacion' => 'Total acumulado de años de servicio institucional más años externos de administración pública.'],
                    ['variable' => 'Sueldo Integral', 'explicacion' => 'Resultado de: Sueldo Base + Total Primas. Es la base para el cálculo.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nSueldo Base: Bs. 5.000,00\nTotal Primas: Bs. 3.200,00\nSueldo Integral: Bs. 8.200,00\nPorcentaje: 100%\nAños de servicio: 25\n\nMonto = 8.200 × (100/100) × 25 = Bs. 205.000,00",
                'observaciones' => 'Aplica a trabajadores con solicitud de jubilación aprobada y expediente completo (100%). La tasa de cambio se congela al momento del cálculo.',
                'activo' => true,
            ],
            [
                'nombre' => 'Prima de Antigüedad',
                'codigo' => 'PRIMA_ANTIGUEDAD',
                'descripcion' => 'Prima que reconoce los años de servicio del trabajador en la institución.',
                'conceptos' => json_encode([
                    'Porcentaje de antigüedad',
                    'Años de servicio',
                ]),
                'variables' => json_encode([
                    'porcentaje_antiguedad' => 'Porcentaje asignado al trabajador por antigüedad',
                    'anios_servicio' => 'Total de años de servicio',
                ]),
                'formula_matematica' => 'Prima = Porcentaje Antigüedad × Años de Servicio',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Porcentaje Antigüedad', 'explicacion' => 'Porcentaje configurado en el perfil del trabajador (campo porcentaje_antiguedad).'],
                    ['variable' => 'Años de Servicio', 'explicacion' => 'Total de años acumulados de servicio del trabajador.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nPorcentaje Antigüedad: 1%\nAños de servicio: 25\n\nPrima = 1 × 25 = 25 (aplicado al valor unitario de la prima familiar)",
                'observaciones' => 'El valor unitario se define en el catálogo de primas.',
                'activo' => true,
            ],
            [
                'nombre' => 'Prima de Hijo',
                'codigo' => 'PRIMA_HIJO',
                'descripcion' => 'Prima por cada hijo dependiente del trabajador.',
                'conceptos' => json_encode([
                    'Número de hijos',
                    'Valor unitario por hijo',
                ]),
                'variables' => json_encode([
                    'numero_hijos' => 'Cantidad de hijos del trabajador',
                    'valor_unitario' => 'Monto asignado por cada hijo (definido en catálogo de primas)',
                ]),
                'formula_matematica' => 'Prima = Número de Hijos × Valor Unitario',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Número de Hijos', 'explicacion' => 'Cantidad de hijos registrados en el perfil del trabajador.'],
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo asignado por cada hijo, configurable en el catálogo de primas.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nNúmero de hijos: 3\nValor unitario: Bs. 120,00\n\nPrima = 3 × 120 = Bs. 360,00",
                'observaciones' => 'Si el trabajador no tiene hijos, la prima es 0.',
                'activo' => true,
            ],
            [
                'nombre' => 'Prima de Hijos con Discapacidad',
                'codigo' => 'PRIMA_HIJOS_DISCAPACIDAD',
                'descripcion' => 'Prima adicional por hijos con discapacidad dependientes del trabajador.',
                'conceptos' => json_encode([
                    'Hijos con discapacidad',
                    'Valor unitario',
                ]),
                'variables' => json_encode([
                    'hijos_discapacidad' => 'Cantidad de hijos con discapacidad',
                    'valor_unitario' => 'Monto asignado por cada hijo con discapacidad',
                ]),
                'formula_matematica' => 'Prima = Hijos Discapacidad × Valor Unitario',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Hijos con Discapacidad', 'explicacion' => 'Cantidad de hijos con discapacidad registrados.'],
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo por cada hijo con discapacidad.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nHijos con discapacidad: 1\nValor unitario: Bs. 180,00\n\nPrima = 1 × 180 = Bs. 180,00",
                'observaciones' => 'La cantidad no puede superar el número total de hijos.',
                'activo' => true,
            ],
            [
                'nombre' => 'Prima de Profesionalización',
                'codigo' => 'PRIMA_PROFESIONALIZACION',
                'descripcion' => 'Prima fija por nivel de formación profesional alcanzado.',
                'conceptos' => json_encode([
                    'Valor fijo de profesionalización',
                ]),
                'variables' => json_encode([
                    'valor_unitario' => 'Monto fijo de la prima de profesionalización',
                ]),
                'formula_matematica' => 'Prima = Valor Unitario',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo definido en el catálogo de primas para profesionalización.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nValor unitario: Bs. 500,00\n\nPrima = Bs. 500,00",
                'observaciones' => 'Monto fijo sin cálculo adicional.',
                'activo' => true,
            ],
            [
                'nombre' => 'Prima de Responsabilidad',
                'codigo' => 'PRIMA_RESPONSABILIDAD',
                'descripcion' => 'Prima para trabajadores que se desempeñan como jefes o coordinadores.',
                'conceptos' => json_encode([
                    'Condición: es jefe/coordinador',
                    'Valor fijo',
                ]),
                'variables' => json_encode([
                    'es_jefe_coordinador' => 'Booleano: true si el trabajador es jefe o coordinador',
                    'valor_unitario' => 'Monto de la prima de responsabilidad',
                ]),
                'formula_matematica' => 'Prima = es_jefe_coordinador ? Valor Unitario : 0',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Es Jefe/Coordinador', 'explicacion' => 'Campo booleano en el perfil del trabajador (es_jefe_coordinador).'],
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo si aplica la condición.'],
                ]),
                'ejemplo_calculo' => "Ejemplo (si es jefe):\nValor unitario: Bs. 800,00\n\nPrima = Bs. 800,00\n\nEjemplo (si NO es jefe):\nPrima = Bs. 0,00",
                'observaciones' => 'Solo aplica si el trabajador tiene asignado el rol de jefe o coordinador.',
                'activo' => true,
            ],
            [
                'nombre' => 'Prima de Actividad Universitaria',
                'codigo' => 'PRIMA_ACTIVIDAD_UNIVERSITARIA',
                'descripcion' => 'Prima para trabajadores que realizan actividad universitaria.',
                'conceptos' => json_encode([
                    'Condición: realiza actividad universitaria',
                    'Valor fijo',
                ]),
                'variables' => json_encode([
                    'actividad_universitaria' => 'Booleano: true si realiza actividad universitaria',
                    'valor_unitario' => 'Monto de la prima',
                ]),
                'formula_matematica' => 'Prima = actividad_universitaria ? Valor Unitario : 0',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Actividad Universitaria', 'explicacion' => 'Campo booleano en el perfil del trabajador.'],
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo si aplica la condición.'],
                ]),
                'ejemplo_calculo' => "Ejemplo (si aplica):\nValor unitario: Bs. 300,00\n\nPrima = Bs. 300,00\n\nEjemplo (si NO aplica):\nPrima = Bs. 0,00",
                'observaciones' => 'Solo aplica si el trabajador realiza actividad universitaria.',
                'activo' => true,
            ],
            [
                'nombre' => 'Cesta Ticket',
                'codigo' => 'CESTA_TICKET',
                'descripcion' => 'Bonificación de cesta ticket asignada al trabajador.',
                'conceptos' => json_encode([
                    'Monto fijo de cesta ticket',
                ]),
                'variables' => json_encode([
                    'valor_unitario' => 'Monto de cesta ticket',
                ]),
                'formula_matematica' => 'Prima = Valor Unitario',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo de cesta ticket configurado en el catálogo de primas.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nValor unitario: Bs. 250,00\n\nPrima = Bs. 250,00",
                'observaciones' => 'Monto fijo mensual.',
                'activo' => true,
            ],
        ];

        foreach ($formulas as $f) {
            DB::table('formulas_prestaciones')->updateOrInsert(
                ['codigo' => $f['codigo']],
                array_merge($f, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
