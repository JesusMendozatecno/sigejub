<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formulas_prestaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->notNull();
            $table->string('codigo', 50)->unique()->notNull();
            $table->text('descripcion')->nullable();
            $table->json('conceptos')->nullable();
            $table->json('variables')->nullable();
            $table->text('formula_matematica')->nullable();
            $table->json('explicacion_variables')->nullable();
            $table->text('ejemplo_calculo')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formulas_prestaciones');
    }
};
