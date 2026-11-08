<?php
// Migración: el expediente digital deja de depender de una solicitud aprobada.
// `solicitud_id` pasa a ser opcional para poder abrir el expediente de un
// trabajador aunque todavía no tenga solicitud (o la tenga en revisión).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            // Se elimina la restricción para poder modificar la columna y luego
            // se vuelve a crear permitiendo valores nulos.
            $table->dropForeign(['solicitud_id']);
        });

        Schema::table('expedientes', function (Blueprint $table) {
            $table->unsignedBigInteger('solicitud_id')->nullable()->change();
        });

        Schema::table('expedientes', function (Blueprint $table) {
            $table->foreign('solicitud_id')
                ->references('id')
                ->on('solicitudes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropForeign(['solicitud_id']);
        });

        Schema::table('expedientes', function (Blueprint $table) {
            $table->unsignedBigInteger('solicitud_id')->nullable(false)->change();
            $table->foreign('solicitud_id')
                ->references('id')
                ->on('solicitudes')
                ->cascadeOnDelete();
        });
    }
};
