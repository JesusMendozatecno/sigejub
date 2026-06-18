<?php
// Migración para recrear expedientes con estructura ampliada y crear la tabla documentos.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('expedientes');

        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->onDelete('cascade');
            $table->foreignId('solicitud_id')->constrained('solicitudes')->onDelete('cascade');
            $table->string('foto_carnet')->nullable();
            $table->integer('estado_global')->default(0);
            $table->text('notas_admin')->nullable();
            $table->timestamps();
        });

        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('archivo');
            $table->enum('estado', ['en_revision', 'aprobado', 'rechazado'])->default('en_revision');
            $table->text('nota_rechazo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('expedientes');
    }
};
