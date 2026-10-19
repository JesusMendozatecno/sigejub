<?php
// Migración para crear la tabla de recibos de pago / comprobantes de nómina
// que se adjuntan al expediente individual de cada trabajador. Permite registrar
// el período, el monto opcional en USD y el archivo digital de consulta posterior.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibos_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->cascadeOnDelete();
            $table->string('periodo', 7)->nullable();            // AAAA-MM
            $table->string('concepto', 150)->nullable();         // Ej.: Nómina enero 2026
            $table->decimal('monto_usd', 12, 2)->nullable();     // Monto opcional en dólares
            $table->string('archivo');                          // PDF o imagen (storage público)
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibos_pago');
    }
};
