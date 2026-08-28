<?php
// Seeder que pobla todas las tablas maestras del sistema SIGEJUB.
// Ejecutar con: php artisan db:seed --class=MasterDataSeeder

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // --- CARGOS ---
        $cargos = [
            ['nombre' => 'Director', 'codigo' => 'DIR', 'activo' => true],
            ['nombre' => 'Subdirector', 'codigo' => 'SUBDIR', 'activo' => true],
            ['nombre' => 'Coordinador', 'codigo' => 'COORD', 'activo' => true],
            ['nombre' => 'Jefe de Departamento', 'codigo' => 'JDEPT', 'activo' => true],
            ['nombre' => 'Analista', 'codigo' => 'ANA', 'activo' => true],
            ['nombre' => 'Asistente', 'codigo' => 'ASI', 'activo' => true],
            ['nombre' => 'Profesor', 'codigo' => 'PROF', 'activo' => true],
            ['nombre' => 'Técnico', 'codigo' => 'TEC', 'activo' => true],
            ['nombre' => 'Administrativo', 'codigo' => 'ADM', 'activo' => true],
            ['nombre' => 'Operativo', 'codigo' => 'OPE', 'activo' => true],
        ];
        DB::table('cargos')->insert($cargos);

        // --- ÁREAS ---
        $areas = [
            ['nombre' => 'Dirección General', 'codigo' => 'DG', 'activo' => true],
            ['nombre' => 'Subdirección Académica', 'codigo' => 'SAC', 'activo' => true],
            ['nombre' => 'Subdirección Administrativa', 'codigo' => 'SAD', 'activo' => true],
            ['nombre' => 'Departamento de Recursos Humanos', 'codigo' => 'DRH', 'activo' => true],
            ['nombre' => 'Departamento de Finanzas', 'codigo' => 'DFI', 'activo' => true],
            ['nombre' => 'Departamento de Sistemas', 'codigo' => 'DSIS', 'activo' => true],
            ['nombre' => 'Departamento de-planeación', 'codigo' => 'DPL', 'activo' => true],
            ['nombre' => 'Departamento de Asuntos Legales', 'codigo' => 'DAL', 'activo' => true],
            ['nombre' => 'Departamento de Mantenimiento', 'codigo' => 'DMA', 'activo' => true],
            ['nombre' => 'Departamento de Logística', 'codigo' => 'DLO', 'activo' => true],
        ];
        DB::table('areas')->insert($areas);

        // --- GRADOS ---
        $grados = [
            ['nombre' => 'P1', 'codigo' => 'P1', 'activo' => true],
            ['nombre' => 'P2', 'codigo' => 'P2', 'activo' => true],
            ['nombre' => 'P3', 'codigo' => 'P3', 'activo' => true],
            ['nombre' => 'P4', 'codigo' => 'P4', 'activo' => true],
            ['nombre' => 'P5', 'codigo' => 'P5', 'activo' => true],
            ['nombre' => 'PA', 'codigo' => 'PA', 'activo' => true],
            ['nombre' => 'PB', 'codigo' => 'PB', 'activo' => true],
            ['nombre' => 'PC', 'codigo' => 'PC', 'activo' => true],
        ];
        DB::table('grados')->insert($grados);

        // --- NIVELES DE INSTRUCCIÓN ---
        $niveles = [
            ['nombre' => 'Bachiller', 'codigo' => 'BAC', 'activo' => true],
            ['nombre' => 'TSU', 'codigo' => 'TSU', 'activo' => true],
            ['nombre' => 'Licenciado', 'codigo' => 'LIC', 'activo' => true],
            ['nombre' => 'Ingeniero', 'codigo' => 'ING', 'activo' => true],
            ['nombre' => 'Especialista', 'codigo' => 'ESP', 'activo' => true],
            ['nombre' => 'Magister', 'codigo' => 'MAG', 'activo' => true],
            ['nombre' => 'Doctor', 'codigo' => 'DOC', 'activo' => true],
        ];
        DB::table('niveles_instruccion')->insert($niveles);

        // --- TIPOS DE CONTRATO ---
        $contratos = [
            ['nombre' => 'Docente fijo', 'codigo' => 'DOC_FIJO', 'activo' => true],
            ['nombre' => 'Ordinario', 'codigo' => 'ORD', 'activo' => true],
            ['nombre' => 'Contratado', 'codigo' => 'CONT', 'activo' => true],
            ['nombre' => 'Medio tiempo', 'codigo' => 'MT', 'activo' => true],
            ['nombre' => 'Tiempo completo', 'codigo' => 'TC', 'activo' => true],
            ['nombre' => 'Por horas', 'codigo' => 'PH', 'activo' => true],
            ['nombre' => 'Interino', 'codigo' => 'INT', 'activo' => true],
        ];
        DB::table('tipos_contrato')->insert($contratos);

        // --- PRIMAS ---
        $primas = [
            ['codigo' => 'PRIMA_FAMILIAR', 'nombre' => 'Prima de Antigüedad', 'monto' => 0, 'activo' => true],
            ['codigo' => 'PRIMA_HIJO', 'nombre' => 'Prima por Hijo', 'monto' => 0, 'activo' => true],
            ['codigo' => 'PRIMA_HIJOS_DISCAPACIDAD', 'nombre' => 'Prima por Hijo con Discapacidad', 'monto' => 0, 'activo' => true],
            ['codigo' => 'PRIMA_PROFESIONALIZACION', 'nombre' => 'Prima de Profesionalización', 'monto' => 0, 'activo' => true],
            ['codigo' => 'PRIMA_RESPONSABILIDAD', 'nombre' => 'Prima de Responsabilidad', 'monto' => 0, 'activo' => true],
            ['codigo' => 'CESTA_TICKET', 'nombre' => 'Cesta Ticket', 'monto' => 0, 'activo' => true],
            ['codigo' => 'PRIMA_ACTIVIDAD_UNIVERSITARIA', 'nombre' => 'Prima de Actividad Universitaria', 'monto' => 0, 'activo' => true],
        ];
        DB::table('primas')->insert($primas);

        // --- TIPOS DE JUBILACIÓN ---
        $jubilaciones = [
            ['nombre' => 'Antigüedad', 'codigo' => 'ANT', 'activo' => true],
            ['nombre' => 'Invalidez', 'codigo' => 'INV', 'activo' => true],
            ['nombre' => 'Especial', 'codigo' => 'ESP', 'activo' => true],
        ];
        DB::table('tipos_jubilacion')->insert($jubilaciones);
    }
}
