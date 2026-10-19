<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('tasa_cambio_id')->nullable()->after('porcentaje_jubilacion');
            $table->decimal('tasa_utilizada', 12, 4)->nullable()->after('tasa_cambio_id');
            $table->string('moneda_tasa', 10)->nullable()->after('tasa_utilizada');
            $table->timestamp('fecha_tasa_utilizada')->nullable()->after('moneda_tasa');
            $table->string('fuente_tasa', 100)->nullable()->after('fecha_tasa_utilizada');
            $table->unsignedBigInteger('calculado_por_user_id')->nullable()->after('fuente_tasa');

            $table->foreign('tasa_cambio_id')->references('id')->on('tasas_cambio')->onDelete('set null');
            $table->foreign('calculado_por_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('prestaciones', function (Blueprint $table) {
            $table->dropForeign(['tasa_cambio_id']);
            $table->dropForeign(['calculado_por_user_id']);
            $table->dropColumn([
                'tasa_cambio_id', 'tasa_utilizada', 'moneda_tasa',
                'fecha_tasa_utilizada', 'fuente_tasa', 'calculado_por_user_id'
            ]);
        });
    }
};
