<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->string('periodo', 20)->nullable()->after('fecha_solicitud');
            $table->string('tipo_jubilacion', 100)->nullable()->after('periodo');
            $table->text('observaciones')->nullable()->after('tipo_jubilacion');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn(['periodo', 'tipo_jubilacion', 'observaciones']);
        });
    }
};
