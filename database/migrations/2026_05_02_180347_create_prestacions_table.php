<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('prestaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('trabajador_id')->constrained('trabajadores')->onDelete('cascade');
        $table->integer('anios_servicio');
        $table->decimal('monto', 12, 2);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('prestaciones');
}
};