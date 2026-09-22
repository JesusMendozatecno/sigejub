<?php
// Migración para crear todas las tablas maestras del sistema SIGEJUB.
// Estas tablas almacenan los catálogos de valores para: cargo, área, grado,
// nivel de instrucción, tipo de contrato, sueldo parametrizado, primas y tipo de jubilación.
// Posteriormente se agregan claves foráneas a la tabla trabajadores.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de Cargos
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('codigo', 50)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de Áreas / Departamentos
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('codigo', 50)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de Grados
        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique(); // P1, P2, P3, etc.
            $table->string('codigo', 20)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de Niveles de Instrucción
        Schema::create('niveles_instruccion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); // TSU, Ingeniero, Licenciado, etc.
            $table->string('codigo', 20)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de Tipos de Contrato
        Schema::create('tipos_contrato', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); // Docente fijo, Ordinario, Contratado, etc.
            $table->string('codigo', 50)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de Sueldos Parametrizados (depende de grado + nivel_instruccion)
        Schema::create('sueldos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')->constrained('grados')->cascadeOnDelete();
            $table->foreignId('nivel_instruccion_id')->constrained('niveles_instruccion')->cascadeOnDelete();
            $table->decimal('sueldo_base', 12, 2);
            $table->decimal('complemento_prima_cargo', 12, 2)->default(0);
            $table->decimal('porcentaje_prima_cargo', 5, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['grado_id', 'nivel_instruccion_id']);
        });

        // Tabla de Primas
        Schema::create('primas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique(); // PRIMA_FAMILIAR, PRIMA_Hijo, etc.
            $table->string('nombre', 150);
            $table->decimal('monto', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de Tipos de Jubilación
        Schema::create('tipos_jubilacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); // Antigüedad, Invalidez, Especial
            $table->string('codigo', 50)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Agregar claves foráneas a la tabla trabajadores
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->foreignId('cargo_id')->nullable()->after('cargo')->constrained('cargos')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->after('unidad_departamento')->constrained('areas')->nullOnDelete();
            $table->foreignId('grado_id')->nullable()->after('grado_nivel')->constrained('grados')->nullOnDelete();
            $table->foreignId('nivel_instruccion_id')->nullable()->after('nivel_instruccion')->constrained('niveles_instruccion')->nullOnDelete();
            $table->foreignId('tipo_contrato_id')->nullable()->after('nivel_educativo_texto')->constrained('tipos_contrato')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->dropForeign(['cargo_id']);
            $table->dropForeign(['area_id']);
            $table->dropForeign(['grado_id']);
            $table->dropForeign(['nivel_instruccion_id']);
            $table->dropForeign(['tipo_contrato_id']);
            $table->dropColumn(['cargo_id', 'area_id', 'grado_id', 'nivel_instruccion_id', 'tipo_contrato_id']);
        });

        Schema::dropIfExists('tipos_jubilacion');
        Schema::dropIfExists('primas');
        Schema::dropIfExists('sueldos');
        Schema::dropIfExists('tipos_contrato');
        Schema::dropIfExists('niveles_instruccion');
        Schema::dropIfExists('grados');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('cargos');
    }
};
