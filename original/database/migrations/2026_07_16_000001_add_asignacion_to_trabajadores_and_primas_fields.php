<?php
// Migración: Agrega campo 'asignacion' a trabajadores y tabla dedicada de primas oficiales.
// 'asignacion' es ENUM: 'Manual' (formulario) o 'Nomina' (importación Excel).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->enum('asignacion', ['Manual', 'Nomina'])->default('Manual')->after('afiliacion_sifaiuty');
        });

        Schema::table('primas', function (Blueprint $table) {
            $table->decimal('valor', 12, 2)->default(0)->after('monto');
            $table->date('fecha_vigencia')->nullable()->after('valor');
        });
    }

    public function down(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->dropColumn('asignacion');
        });

        Schema::table('primas', function (Blueprint $table) {
            $table->dropColumn(['valor', 'fecha_vigencia']);
        });
    }
};
