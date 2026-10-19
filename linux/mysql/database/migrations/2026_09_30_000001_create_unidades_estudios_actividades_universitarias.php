<?php
// Migración para añadir el maestro de Unidades/Departamentos, la relación de
// tipo de jubilación en trabajadores, y las tablas hijas de estudios y de
// actividades universitarias (tipo, lugar y fechas de vigencia de la prima).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('codigo', 50)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('trabajadores', function (Blueprint $table) {
            $table->unsignedBigInteger('unidad_id')->nullable()->after('unidad_departamento');
            $table->unsignedBigInteger('tipo_jubilacion_id')->nullable()->after('tipo_nomina');
        });

        Schema::table('trabajadores', function (Blueprint $table) {
            $table->foreign('unidad_id')->references('id')->on('unidades')->nullOnDelete();
            $table->foreign('tipo_jubilacion_id')->references('id')->on('tipos_jubilacion')->nullOnDelete();
        });

        Schema::create('estudios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->cascadeOnDelete();
            $table->unsignedBigInteger('nivel_instruccion_id')->nullable();
            $table->string('especialidad', 150)->nullable();
            $table->string('casa_estudio', 150)->nullable();
            $table->string('casa_egreso', 150)->nullable();
            $table->timestamps();

            $table->foreign('nivel_instruccion_id')->references('id')->on('niveles_instruccion')->nullOnDelete();
        });

        Schema::create('actividades_universitarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->cascadeOnDelete();
            $table->string('tipo', 150)->nullable();
            $table->string('lugar', 150)->nullable();
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades_universitarias');
        Schema::dropIfExists('estudios');

        Schema::table('trabajadores', function (Blueprint $table) {
            $table->dropForeign(['unidad_id']);
            $table->dropForeign(['tipo_jubilacion_id']);
            $table->dropColumn(['unidad_id', 'tipo_jubilacion_id']);
        });

        Schema::dropIfExists('unidades');
    }
};