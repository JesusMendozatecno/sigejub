<?php
// Migración para crear la tabla prestaciones_sociales con cálculo de antigüedad e intereses.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestaciones_sociales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->onDelete('cascade');
            $table->date('fecha_calculo');
            $table->decimal('salario_integral_promedio', 12, 2)->nullable();
            $table->integer('antiguedad_dias')->nullable();
            $table->decimal('antiguedad_monto', 12, 2)->nullable();
            $table->decimal('intereses_prestaciones', 12, 2)->nullable();
            $table->decimal('total_prestaciones', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestaciones_sociales');
    }
};
