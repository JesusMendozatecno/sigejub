<?php
// Migración para trocear el contenido binario (base64) de `archivos_bd` en
// bloques pequeños.
//
// Motivo: MySQL/MariaDB limita el tamaño de un paquete SQL según
// `max_allowed_packet` (por defecto 1 MB en XAMPP). Un PDF de ~1.4 MB codificado
// en base64 (~1.9 MB) supera ese límite y provoca "MySQL server has gone away",
// abortando el respaldo. Al dividir el contenido en bloques de ~240 KB ninguna
// sentencia INSERT/UPDATE se acerca al límite, sin importar la configuración del
// servidor. La columna `contenido` de `archivos_bd` se mantiene por
// compatibilidad (lectura de respaldos antiguos) pero los nuevos usan bloques.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos_bd_bloques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('archivo_bd_id');
            $table->unsignedInteger('orden');          // Índice del bloque (0-based)
            $table->longText('datos');                 // Fragmento del base64
            $table->timestamps();

            $table->index(['archivo_bd_id', 'orden']);
            $table->foreign('archivo_bd_id')
                ->references('id')->on('archivos_bd')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos_bd_bloques');
    }
};
