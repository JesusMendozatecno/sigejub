<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormulaPrestacionSeeder extends Seeder
{
    public function run(): void
    {
        // La antigüedad se documenta bajo el código real del catálogo de primas.
        DB::table('formulas_prestaciones')->where('codigo', 'PRIMA_ANTIGUEDAD')->delete();

        $formulas = [
            [
                'nombre' => 'Prestación de Antigüedad (Monto Total)',
                'codigo' => 'PRESTACION_ANTIGUEDAD',
                'descripcion' => 'Monto total de prestaciones sociales del trabajador según la legislación laboral venezolana vigente y el tabulador institucional.',
                'conceptos' => json_encode([
                    'Sueldo Base',
                    'Total Primas',
                    'Sueldo Integral',
                    'Porcentaje de Jubilación',
                    'Años de servicio',
                ]),
                'variables' => json_encode([
                    'sueldo_base' => 'Sueldo base mensual del trabajador en bolívares',
                    'total_primas' => 'Suma de todas las primas salariales aplicables (convertidas a Bs)',
                    'sueldo_integral' => 'Resultado de: Sueldo Base + Total Primas',
                    'porcentaje_jubilacion' => 'Porcentaje aplicable: 100% (jubilación) o 82,5% (incapacidad)',
                    'anios_servicio' => 'Total de años de servicio del trabajador',
                ]),
                'formula_matematica' => 'Monto = Sueldo Integral × (Porcentaje / 100) × Años de Servicio',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Sueldo Base', 'explicacion' => 'Salario mensual nominal del trabajador. Se ingresa manualmente o se obtiene del tabulador de sueldos.'],
                    ['variable' => 'Total Primas', 'explicacion' => 'Suma de todas las primas aplicables: familiar/antigüedad, hijos, hijos con discapacidad, profesionalización, responsabilidad, cesta ticket y actividad universitaria. Se calculan en $ y se convierten a Bs con la tasa de cambio del día.'],
                    ['variable' => 'Sueldo Integral', 'explicacion' => 'Resultado de: Sueldo Base + Total Primas. Es la base del cálculo.'],
                    ['variable' => 'Porcentaje de Jubilación', 'explicacion' => '100% para jubilación ordinaria, 82,5% para casos por incapacidad.'],
                    ['variable' => 'Años de Servicio', 'explicacion' => 'Total acumulado de años de servicio del trabajador (total_anos_servicio).'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nSueldo Base: Bs. 5.000,00\nTotal Primas: Bs. 3.200,00\nSueldo Integral: Bs. 8.200,00\nPorcentaje: 100%\nAños de servicio: 25\n\nMonto = 8.200 × (100 / 100) × 25 = Bs. 205.000,00",
                'observaciones' => 'Aplica a trabajadores con solicitud de jubilación aprobada y expediente completo (100%). La tasa de cambio se congela al momento del cálculo.',
                'activo' => true,
            ],
            [
                'nombre' => 'Sueldo Integral Mensual',
                'codigo' => 'SUELDO_INTEGRAL',
                'descripcion' => 'Composición del sueldo integral: salario base más el total de primas salariales aplicables.',
                'conceptos' => json_encode([
                    'Sueldo Base',
                    'Total Primas',
                ]),
                'variables' => json_encode([
                    'sueldo_base' => 'Sueldo base mensual del trabajador en bolívares',
                    'total_primas' => 'Suma de todas las primas aplicables (en Bs)',
                ]),
                'formula_matematica' => 'Sueldo Integral = Sueldo Base + Total Primas',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Sueldo Base', 'explicacion' => 'Salario mensual nominal del trabajador (Bs).'],
                    ['variable' => 'Total Primas', 'explicacion' => 'Suma de las primas salariales aplicables, ya convertidas a bolívares.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nSueldo Base: Bs. 5.000,00\nTotal Primas: Bs. 3.200,00\n\nSueldo Integral = 5.000 + 3.200 = Bs. 8.200,00",
                'observaciones' => 'Base para el cálculo de la prestación de antigüedad y para la nómina.',
                'activo' => true,
            ],
            [
                'nombre' => 'Prima de Antigüedad (Familiar)',
                'codigo' => 'PRIMA_FAMILIAR',
                'descripcion' => 'Prima que reconoce los años de servicio del trabajador en la institución.',
                'conceptos' => json_encode([
                    'Valor unitario de la prima',
                    'Años de servicio',
                ]),
                'variables' => json_encode([
                    'valor_unitario' => 'Valor en $ definido en el catálogo de primas (PRIMA_FAMILIAR)',
                    'anios_servicio' => 'Total de años de servicio del trabajador',
                ]),
                'formula_matematica' => 'Prima = Valor Unitario × Años de Servicio',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto por año de servicio definido en el catálogo de primas (parámetro PRIMA_FAMILIAR). Se ingresa en dólares.'],
                    ['variable' => 'Años de Servicio', 'explicacion' => 'Total de años acumulados de servicio del trabajador (total_anos_servicio).'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nValor unitario: $ 1,00\nAños de servicio: 25\n\nPrima = 1,00 × 25 = $ 25,00\nEn Bs = 25,00 × tasa (ej. 150,00) = Bs. 3.750,00",
                'observaciones' => 'El valor unitario se define en el catálogo de primas. Se calcula en dólares y se convierte a bolívares con la tasa del día.',
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
                    'valor_unitario' => 'Monto por hijo (definido en el catálogo de primas)',
                ]),
                'formula_matematica' => 'Prima = Número de Hijos × Valor Unitario',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Número de Hijos', 'explicacion' => 'Cantidad de hijos registrados en el perfil del trabajador.'],
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo por cada hijo (catálogo de primas), en dólares.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nNúmero de hijos: 3\nValor unitario: $ 10,00\n\nPrima = 3 × 10 = $ 30,00\nEn Bs = 30,00 × tasa (ej. 150,00) = Bs. 4.500,00",
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
                    'valor_unitario' => 'Monto por cada hijo con discapacidad',
                ]),
                'formula_matematica' => 'Prima = Hijos con Discapacidad × Valor Unitario',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Hijos con Discapacidad', 'explicacion' => 'Cantidad de hijos con discapacidad registrados en el perfil.'],
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo por cada hijo con discapacidad (catálogo de primas), en dólares.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nHijos con discapacidad: 1\nValor unitario: $ 20,00\n\nPrima = 1 × 20 = $ 20,00\nEn Bs = 20,00 × tasa (ej. 150,00) = Bs. 3.000,00",
                'observaciones' => 'La cantidad no puede superar el número total de hijos.',
                'activo' => true,
            ],
            [
                'nombre' => 'Prima de Profesionalización',
                'codigo' => 'PRIMA_PROFESIONALIZACION',
                'descripcion' => 'Prima calculada sobre el sueldo base según el nivel académico alcanzado por el trabajador.',
                'conceptos' => json_encode([
                    'Sueldo base del trabajador',
                    'Nivel de profesionalización',
                ]),
                'variables' => json_encode([
                    'sueldo_base' => 'Sueldo base del trabajador en bolívares',
                    'nivel_profesionalizacion' => 'Porcentaje según escala: TSU 11%, Lic/Ing 13%, Esp 15%, Mag 20%, Doc 25%',
                ]),
                'formula_matematica' => 'Prima = Sueldo Base × (Nivel de Profesionalización / 100)',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Sueldo Base', 'explicacion' => 'Sueldo base del trabajador (en Bs), tomado de su perfil.'],
                    ['variable' => 'Nivel de Profesionalización', 'explicacion' => 'Porcentaje correspondiente al título académico más alto (ver ESCALA_PROFESIONALIZACION).'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nNivel: Magister (20%)\nSueldo Base: Bs. 10.000,00\n\nPrima = 10.000 × (20 / 100) = Bs. 2.000,00",
                'observaciones' => 'Se aplica SIEMPRE el nivel más alto alcanzado; los niveles NO se suman entre sí. Se calcula directamente en bolívares sobre el sueldo base del trabajador.',
                'activo' => true,
            ],
            [
                'nombre' => 'Escala de Profesionalización',
                'codigo' => 'ESCALA_PROFESIONALIZACION',
                'descripcion' => 'Escala de porcentajes por nivel académico para la prima de profesionalización. Se aplica únicamente el máximo alcanzado.',
                'conceptos' => json_encode([
                    'Doctor / PhD: 25%',
                    'Magister: 20%',
                    'Especialista: 15%',
                    'Licenciado / Ingeniero: 13%',
                    'TSU: 11%',
                ]),
                'variables' => json_encode([
                    'doctorado' => '25% para Doctor o PhD',
                    'magister' => '20% para Magister',
                    'especialista' => '15% para Especialista o Especialización',
                    'licenciado_ingeniero' => '13% para Licenciado, Licenciatura, Ingeniero o Ingeniería',
                    'tsu' => '11% para TSU o Técnico Superior Universitario',
                ]),
                'formula_matematica' => 'Nivel% = Escala(título académico) → Prima = Sueldo Base × (Nivel% / 100)',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Doctor / PhD', 'explicacion' => '25% del sueldo base. Identifica doctor, doctorado o PhD.'],
                    ['variable' => 'Magister', 'explicacion' => '20% del sueldo base.'],
                    ['variable' => 'Especialista', 'explicacion' => '15% del sueldo base. Especialista o especialización.'],
                    ['variable' => 'Licenciado / Ingeniero', 'explicacion' => '13% del sueldo base. Licenciado(a), licenciatura, ingeniero(a) o ingeniería.'],
                    ['variable' => 'TSU', 'explicacion' => '11% del sueldo base. Técnico Superior Universitario o TSU.'],
                ]),
                'ejemplo_calculo' => "Ejemplo (cruce de niveles):\nEl trabajador es Licenciado (13%) y además Especialista (15%).\n\nSe aplica el mayor: 15%\nPrima = Sueldo Base × 0,15 (no se suma 13% + 15%)",
                'observaciones' => 'Si no corresponde ningún nivel (p. ej. Bachiller), la prima de profesionalización es 0.',
                'activo' => true,
            ],
            [
                'nombre' => 'Prima de Responsabilidad',
                'codigo' => 'PRIMA_RESPONSABILIDAD',
                'descripcion' => 'Prima para trabajadores que se desempeñan como jefes o coordinadores.',
                'conceptos' => json_encode([
                    'Condición: es jefe/coordinador',
                    'Valor unitario',
                ]),
                'variables' => json_encode([
                    'es_jefe_coordinador' => 'Booleano: true si el trabajador es jefe o coordinador',
                    'valor_unitario' => 'Monto de la prima de responsabilidad',
                ]),
                'formula_matematica' => 'Prima = Si es Jefe/Coordinador → Valor Unitario, si no → 0',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Es Jefe/Coordinador', 'explicacion' => 'Campo booleano en el perfil del trabajador (es_jefe_coordinador).'],
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo si aplica la condición (catálogo de primas), en dólares.'],
                ]),
                'ejemplo_calculo' => "Ejemplo (si es jefe):\nValor unitario: $ 50,00\n\nPrima = $ 50,00\nEn Bs = 50,00 × tasa (ej. 150,00) = Bs. 7.500,00\n\nEjemplo (si NO es jefe):\nPrima = $ 0,00",
                'observaciones' => 'Solo aplica si el trabajador tiene asignado el rol de jefe o coordinador.',
                'activo' => true,
            ],
            [
                'nombre' => 'Prima de Actividad Universitaria',
                'codigo' => 'PRIMA_ACTIVIDAD_UNIVERSITARIA',
                'descripcion' => 'Prima para trabajadores que realizan actividad universitaria.',
                'conceptos' => json_encode([
                    'Condición: realiza actividad universitaria',
                    'Valor unitario',
                ]),
                'variables' => json_encode([
                    'actividad_universitaria' => 'Booleano: true si realiza actividad universitaria',
                    'valor_unitario' => 'Monto de la prima',
                ]),
                'formula_matematica' => 'Prima = Si realiza Actividad Universitaria → Valor Unitario, si no → 0',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Actividad Universitaria', 'explicacion' => 'Campo booleano en el perfil del trabajador (actividad_universitaria).'],
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo si aplica la condición (catálogo de primas), en dólares.'],
                ]),
                'ejemplo_calculo' => "Ejemplo (si aplica):\nValor unitario: $ 15,00\n\nPrima = $ 15,00\nEn Bs = 15,00 × tasa (ej. 150,00) = Bs. 2.250,00\n\nEjemplo (si NO aplica):\nPrima = $ 0,00",
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
                    'valor_unitario' => 'Monto de cesta ticket (en dólares)',
                ]),
                'formula_matematica' => 'Prima = Valor Unitario',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Valor Unitario', 'explicacion' => 'Monto fijo de cesta ticket configurado en el catálogo de primas, en dólares.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nValor unitario: $ 40,00\n\nPrima = $ 40,00\nEn Bs = 40,00 × tasa (ej. 150,00) = Bs. 6.000,00",
                'observaciones' => 'Monto fijo mensual.',
                'activo' => true,
            ],
            [
                'nombre' => 'Conversión USD → VES (Bolívares)',
                'codigo' => 'CONVERSION_USD_VES',
                'descripcion' => 'Conversión de montos en dólares a bolívares usando la tasa de cambio oficial, y viceversa para la presentación en la moneda elegida.',
                'conceptos' => json_encode([
                    'Monto en dólares',
                    'Tasa de cambio',
                ]),
                'variables' => json_encode([
                    'monto_usd' => 'Monto en dólares (primas y sueldo base cuando se trabaja en $)',
                    'tasa' => 'Tasa de cambio oficial VES/USD (Bs por dólar)',
                ]),
                'formula_matematica' => 'Bs = $ × Tasa   |   $ = Bs ÷ Tasa',
                'explicacion_variables' => json_encode([
                    ['variable' => 'Monto en $', 'explicacion' => 'Valor en dólares que se desea convertir.'],
                    ['variable' => 'Tasa', 'explicacion' => 'Tasa de cambio oficial (VES/USD), redondeada a 2 decimales.'],
                ]),
                'ejemplo_calculo' => "Ejemplo:\nMonto: $ 25,00\nTasa: Bs. 150,00\n\nBs = 25 × 150 = Bs. 3.750,00\n\n(En modo $: $ = 3.750 / 150 = $ 25,00)",
                'observaciones' => 'El cálculo interno del sistema siempre trabaja en bolívares. Los valores del catálogo de primas se almacenan en dólares.',
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