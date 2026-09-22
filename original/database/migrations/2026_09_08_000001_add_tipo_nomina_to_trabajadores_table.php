<?php
// Clasifica al trabajador por la hoja de nómina de la que proviene (ADM, DOC, OBREROS).
// Permite que las pestañas del módulo de nómina filtren y que el exportado ubique
// a cada trabajador en su hoja correcta.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->string('tipo_nomina', 20)->nullable()->index()->after('tipo_contrato_id');
        });
    }

    public function down(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->dropIndex(['tipo_nomina']);
            $table->dropColumn('tipo_nomina');
        });
    }
};