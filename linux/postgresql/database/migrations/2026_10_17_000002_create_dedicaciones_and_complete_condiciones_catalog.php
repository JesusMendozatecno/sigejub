<?php
// Migración para separar la condición laboral de la dedicación docente.
// Crea el catálogo `dedicaciones` (tiempo convencional, medio tiempo, tiempo
// completo y dedicación exclusiva) y completa el catálogo `tipos_contrato`
// —que funciona como condición laboral— con las condiciones docentes faltantes.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dedicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); // Tiempo convencional, Medio tiempo, etc.
            $table->string('codigo', 50)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $ahora = now();
        $dedicaciones = [
            ['nombre' => 'Tiempo convencional', 'codigo' => 'DED_CONV', 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Medio tiempo', 'codigo' => 'DED_MEDIO', 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Tiempo completo', 'codigo' => 'DED_COMPLETO', 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Dedicación exclusiva', 'codigo' => 'DED_EXCLUSIVA', 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
        ];
        foreach ($dedicaciones as $dedicacion) {
            $existe = DB::table('dedicaciones')->where('codigo', $dedicacion['codigo'])->exists();
            if (!$existe) {
                DB::table('dedicaciones')->insert($dedicacion);
            }
        }

        // Condiciones laborales docentes requeridas que aún no existen en tipos_contrato
        $condiciones = [
            ['nombre' => 'Docente contratado', 'codigo' => 'DOC_CONT'],
            ['nombre' => 'Docente ordinario', 'codigo' => 'DOC_ORD'],
        ];
        foreach ($condiciones as $condicion) {
            $existe = DB::table('tipos_contrato')
                ->where('codigo', $condicion['codigo'])
                ->orWhere('nombre', $condicion['nombre'])
                ->exists();
            if (!$existe) {
                DB::table('tipos_contrato')->insert([
                    'nombre' => $condicion['nombre'],
                    'codigo' => $condicion['codigo'],
                    'activo' => true,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }
        }

        // Las dedicaciones registradas en tipos_contrato (medio tiempo, tiempo
        // completo, por horas) no son condiciones laborales: se desactivan para
        // que no se ofrezcan como condición en el formulario del trabajador.
        DB::table('tipos_contrato')
            ->whereIn('codigo', ['MT', 'TC', 'PH'])
            ->update(['activo' => false]);
    }

    public function down(): void
    {
        DB::table('tipos_contrato')
            ->whereIn('codigo', ['MT', 'TC', 'PH'])
            ->update(['activo' => true]);
        DB::table('tipos_contrato')
            ->whereIn('codigo', ['DOC_CONT', 'DOC_ORD'])
            ->delete();

        Schema::dropIfExists('dedicaciones');
    }
};
