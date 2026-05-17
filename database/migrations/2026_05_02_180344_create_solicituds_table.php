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
    Schema::create('solicitudes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('trabajador_id')->constrained('trabajadores')->onDelete('cascade');
        $table->date('fecha_solicitud');
        $table->enum('estado', ['pendiente', 'revision', 'aprobado', 'rechazado']);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('solicitudes');
}
};