<?php
// Migración para registrar el monto de fideicomiso de forma manual en las
// prestaciones. Se almacena aparte del monto calculado para mostrarlo en el
// resumen por separado y nunca alterar el cálculo de las prestaciones.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestaciones', function (Blueprint $table) {
            $table->decimal('fideicomiso_monto', 12, 2)->nullable();
            $table->text('fideicomiso_observaciones')->nullable();
            $table->string('fideicomiso_documento')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('prestaciones', function (Blueprint $table) {
            $table->dropColumn(['fideicomiso_monto', 'fideicomiso_observaciones', 'fideicomiso_documento']);
        });
    }
};
