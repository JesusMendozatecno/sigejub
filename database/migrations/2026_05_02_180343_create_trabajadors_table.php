<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
    {
     Schema::create('trabajadores', function (Blueprint $table) {
    $table->id();
    $table->string('cedula')->unique();
    $table->string('nombres');
    $table->string('apellidos');
    $table->string('cuenta_bancaria')->nullable(); // Nro de cuenta Banco de Venezuela
    $table->enum('genero', ['M', 'F']);
    
    // Datos de Clasificación y Cargo
    $table->string('grado_nivel'); // Grado Nivel Tabulador
    $table->string('cargo');
    $table->string('unidad_departamento'); // Jefe y/o Coordinadores de Unidad
    
    // Fechas y Tiempos
    $table->date('fecha_nacimiento');
    $table->integer('edad');
    $table->date('fecha_ingreso');
    $table->integer('anos_servicio_inst'); // Años en la institución
    $table->integer('anos_servicio_externo'); // Años en administración pública
    $table->integer('total_anos_servicio');
    
    // Instrucción y Familia
    $table->integer('nivel_instruccion'); // 1-TSU, 2-Ing/Lic, etc.
    $table->integer('numero_hijos');
    $table->integer('hijos_discapacidad')->default(0);
    
    // Porcentajes
    $table->decimal('porcentaje_antiguedad', 5, 2)->default(0);
    $table->decimal('porcentaje_caja_ahorro', 5, 2)->default(0);
    
    $table->timestamps();
    $table->softDeletes(); // Para no borrar datos si se jubilan o retiran
});

    }
    /**
     * Reverse the migrations.
     */
        public function down(): void
        {
            Schema::dropIfExists('trabajadores');
        }

};