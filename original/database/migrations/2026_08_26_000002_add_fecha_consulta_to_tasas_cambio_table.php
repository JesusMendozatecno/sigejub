<?php
// Migración: agrega columna fecha_consulta a la tabla tasas_cambio.
// Permite rastrear cuándo se consultó la tasa (útil para indicadores de frescura 🟢🟡🔴).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasas_cambio', function (Blueprint $table) {
            if (!Schema::hasColumn('tasas_cambio', 'fecha_consulta')) {
                $table->timestamp('fecha_consulta')->nullable()->after('activa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasas_cambio', function (Blueprint $table) {
            if (Schema::hasColumn('tasas_cambio', 'fecha_consulta')) {
                $table->dropColumn('fecha_consulta');
            }
        });
    }
};
