<?php
// Migración: Agregar SoftDeletes a la tabla solicitudes.
// Permite bajas lógicas de solicitudes sin eliminar datos físicos.
// La columna deleted_at se crea nullable; null = activa, timestamp = eliminada.

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
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
