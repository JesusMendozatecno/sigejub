<?php
// Migración mínima para añadir método HTTP y ruta al registro de auditoría (Caja Negra).
// Estrictamente relacionada con el módulo Historial/Caja Negra.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'metodo')) {
                $table->string('metodo', 10)->nullable()->after('navegador');
            }
            if (!Schema::hasColumn('activities', 'ruta')) {
                $table->string('ruta', 255)->nullable()->after('metodo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            if (Schema::hasColumn('activities', 'metodo')) {
                $table->dropColumn('metodo');
            }
            if (Schema::hasColumn('activities', 'ruta')) {
                $table->dropColumn('ruta');
            }
        });
    }
};