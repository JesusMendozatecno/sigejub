<?php
// Migración: cláusulas de la convención colectiva.
//
// Una cláusula de convención colectiva es un beneficio acordado en el contrato
// colectivo de los trabajadores (por ejemplo la Cláusula 55 de la IV Convención
// Colectiva Única de Trabajadores del Sector Universitario, que fija la pensión
// de jubilación en el 100% o 82.5% del último salario). Además de esos
// porcentajes, la convención puede otorgar montos adicionales que se suman al
// cálculo de prestaciones sociales (días de salario, porcentajes del sueldo o
// montos fijos).
//
// Se crean:
//  - clausulas_convencion: catálogo configurable (número, nombre, tipo de cálculo, valor).
//  - expediente_clausula:  cláusulas aplicables a cada expediente.
//  - trabajadores.clausulas_convencion: selección previa mientras el trabajador
//    todavía no tiene expediente; se copia al crear el expediente.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clausulas_convencion', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30);                       // Ej: "55"
            $table->string('nombre', 150);                      // Ej: "Pensión de jubilación"
            $table->text('descripcion')->nullable();            // Texto de la cláusula
            // porcentaje_sueldo_base | porcentaje_sueldo_integral | dias_sueldo_integral | monto_fijo | informativa
            $table->string('tipo_calculo', 40)->default('informativa');
            $table->decimal('valor', 14, 4)->default(0);        // % , días o monto en Bs.
            $table->boolean('por_anio')->default(false);        // multiplica el resultado por años de servicio
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('numero');
        });

        Schema::create('expediente_clausula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->onDelete('cascade');
            $table->foreignId('clausula_id')->constrained('clausulas_convencion')->onDelete('cascade');
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->unique(['expediente_id', 'clausula_id']);
        });

        if (!Schema::hasColumn('trabajadores', 'clausulas_convencion')) {
            Schema::table('trabajadores', function (Blueprint $table) {
                $table->json('clausulas_convencion')->nullable()->after('tipo_jubilacion_id');
            });
        }

        // Cláusula 55 verificada: IV Convención Colectiva Única de Trabajadores
        // del Sector Universitario (100% / 82.5% del último salario devengado).
        // Es informativa: el porcentaje se elige en el cálculo, no suma un monto.
        $existe = DB::table('clausulas_convencion')->where('numero', '55')->exists();
        if (!$existe) {
            DB::table('clausulas_convencion')->insert([
                'numero' => '55',
                'nombre' => 'Pensión de jubilación (IV Convención Colectiva del Sector Universitario)',
                'descripcion' => 'El monto de la pensión será el 100% del último salario devengado cuando el trabajador cumpla 25 años de servicio en la administración pública (10 de ellos en la docencia universitaria), o 25 años de servicio en la institución. Se otorga el 82.5% cuando corresponde la jubilación por incapacidad. El porcentaje se elige en el cálculo de prestaciones (100% / 82.5%).',
                'tipo_calculo' => 'informativa',
                'valor' => 0,
                'por_anio' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_clausula');
        Schema::dropIfExists('clausulas_convencion');

        if (Schema::hasColumn('trabajadores', 'clausulas_convencion')) {
            Schema::table('trabajadores', function (Blueprint $table) {
                $table->dropColumn('clausulas_convencion');
            });
        }
    }
};
