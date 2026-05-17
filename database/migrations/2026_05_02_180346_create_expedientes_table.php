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
    Schema::create('expedientes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('solicitud_id')->constrained('solicitudes')->onDelete('cascade');
        $table->string('documento');
        $table->string('tipo_documento');
        $table->enum('estado', ['pendiente', 'validado', 'rechazado']);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('expedientes');
}

};
