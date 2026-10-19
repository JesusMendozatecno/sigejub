<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestaciones', function (Blueprint $table) {
            $table->json('detalles')->nullable()->after('monto');
            $table->decimal('sueldo_integral', 12, 2)->default(0)->after('detalles');
            $table->decimal('total_primas', 12, 2)->default(0)->after('sueldo_integral');
            $table->decimal('porcentaje_jubilacion', 5, 2)->default(100)->after('total_primas');
        });
    }

    public function down(): void
    {
        Schema::table('prestaciones', function (Blueprint $table) {
            $table->dropColumn(['detalles', 'sueldo_integral', 'total_primas', 'porcentaje_jubilacion']);
        });
    }
};
