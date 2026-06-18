<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->string('codigo_empleado', 20)->nullable()->after('id');
            $table->decimal('sueldo_base', 12, 2)->nullable()->after('unidad_departamento');
            $table->string('denominacion_salario')->nullable()->after('sueldo_base');
            $table->string('tabulador', 50)->nullable()->after('denominacion_salario');
            $table->decimal('porcentaje_prima_cargo', 5, 2)->nullable()->after('tabulador');
            $table->decimal('complemento_prima_cargo', 12, 2)->nullable()->after('porcentaje_prima_cargo');
            $table->boolean('es_jefe_coordinador')->default(false)->after('complemento_prima_cargo');
            $table->decimal('cesta_ticket', 12, 2)->nullable()->after('es_jefe_coordinador');
            $table->decimal('prima_profesionalizacion', 12, 2)->nullable()->after('cesta_ticket');
            $table->decimal('sugau', 12, 2)->nullable()->after('prima_profesionalizacion');
            $table->string('afiliacion_sifaiuty', 50)->nullable()->after('sugau');
            $table->string('nivel_educativo_texto', 100)->nullable()->after('nivel_instruccion');
        });
    }

    public function down(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->dropColumn([
                'codigo_empleado',
                'sueldo_base',
                'denominacion_salario',
                'tabulador',
                'porcentaje_prima_cargo',
                'complemento_prima_cargo',
                'es_jefe_coordinador',
                'cesta_ticket',
                'prima_profesionalizacion',
                'sugau',
                'afiliacion_sifaiuty',
                'nivel_educativo_texto',
            ]);
        });
    }
};
