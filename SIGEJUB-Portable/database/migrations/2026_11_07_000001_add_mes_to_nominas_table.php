<?php
// Migración: la nómina pasa de organizarse por año a organizarse por mes.
// `periodo` sigue guardando el año (compatibilidad con los registros existentes)
// y `mes` (1-12) identifica la planilla mensual. Una nómina anual antigua queda
// con `mes` nulo hasta que se cargue el primer mes.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('nominas', 'mes')) {
            Schema::table('nominas', function (Blueprint $table) {
                $table->unsignedTinyInteger('mes')->nullable()->after('periodo');
                $table->index(['periodo', 'mes'], 'nominas_periodo_mes_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nominas', 'mes')) {
            Schema::table('nominas', function (Blueprint $table) {
                $table->dropIndex('nominas_periodo_mes_index');
                $table->dropColumn('mes');
            });
        }
    }
};
