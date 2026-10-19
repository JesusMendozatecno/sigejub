<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('nominas');

        Schema::create('nominas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('periodo', 10);
            $table->string('estado', 20)->default('borrador');
            $table->decimal('total_general', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('nomina_trabajador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomina_id')->constrained('nominas')->onDelete('cascade');
            $table->foreignId('trabajador_id')->constrained('trabajadores')->onDelete('cascade');
            $table->decimal('sueldo_base', 12, 2)->nullable();
            $table->decimal('prima_familiar', 12, 2)->nullable();
            $table->decimal('prima_hijo', 12, 2)->nullable();
            $table->decimal('prima_hijos_discapacidad', 12, 2)->nullable();
            $table->decimal('prima_actividad_universitaria', 12, 2)->nullable();
            $table->decimal('prima_profesionalizacion', 12, 2)->nullable();
            $table->decimal('prima_responsabilidad', 12, 2)->nullable();
            $table->decimal('complemento_prima_responsabilidad', 12, 2)->nullable();
            $table->decimal('prima_antiguedad', 12, 2)->nullable();
            $table->decimal('cesta_ticket', 12, 2)->nullable();
            $table->decimal('total_asignacion', 12, 2)->nullable();
            $table->decimal('sso', 12, 2)->nullable();
            $table->decimal('lpf', 12, 2)->nullable();
            $table->decimal('faov', 12, 2)->nullable();
            $table->decimal('aporte_ipasme', 12, 2)->nullable();
            $table->decimal('aporte_caja_ahorro', 12, 2)->nullable();
            $table->decimal('prestamo_caja_ahorro', 12, 2)->nullable();
            $table->decimal('isr', 12, 2)->default(0);
            $table->decimal('horas_extras', 12, 2)->default(0);
            $table->decimal('total_deduccion', 12, 2)->nullable();
            $table->decimal('neto_a_cobrar', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['nomina_id', 'trabajador_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomina_trabajador');
        Schema::dropIfExists('nominas');

        Schema::create('nominas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->onDelete('cascade');
            $table->date('periodo');
            $table->decimal('sueldo_base', 12, 2)->nullable();
            $table->decimal('prima_familiar', 12, 2)->nullable();
            $table->decimal('prima_hijo', 12, 2)->nullable();
            $table->decimal('prima_hijos_discapacidad', 12, 2)->nullable();
            $table->decimal('prima_actividad_universitaria', 12, 2)->nullable();
            $table->decimal('prima_profesionalizacion', 12, 2)->nullable();
            $table->decimal('prima_responsabilidad', 12, 2)->nullable();
            $table->decimal('complemento_prima_responsabilidad', 12, 2)->nullable();
            $table->decimal('prima_antiguedad', 12, 2)->nullable();
            $table->decimal('cesta_ticket', 12, 2)->nullable();
            $table->decimal('total_asignacion', 12, 2)->nullable();
            $table->decimal('sso', 12, 2)->nullable();
            $table->decimal('lpf', 12, 2)->nullable();
            $table->decimal('faov', 12, 2)->nullable();
            $table->decimal('aporte_ipasme', 12, 2)->nullable();
            $table->decimal('aporte_caja_ahorro', 12, 2)->nullable();
            $table->decimal('prestamo_caja_ahorro', 12, 2)->nullable();
            $table->decimal('isr', 12, 2)->default(0);
            $table->decimal('horas_extras', 12, 2)->default(0);
            $table->decimal('total_deduccion', 12, 2)->nullable();
            $table->decimal('neto_a_cobrar', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['trabajador_id', 'periodo']);
        });
    }
};
