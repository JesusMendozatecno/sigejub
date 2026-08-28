<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasas_cambio', function (Blueprint $table) {
            $table->id();
            $table->decimal('tasa', 12, 4)->notNull();
            $table->string('moneda_origen', 10)->default('USD');
            $table->string('moneda_destino', 10)->default('VES');
            $table->string('fuente', 100)->nullable();
            $table->enum('tipo', ['automatica', 'manual'])->default('manual');
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('set null');
            $table->index('activa');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasas_cambio');
    }
};
