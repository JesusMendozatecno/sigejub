<?php
// Migración para respaldar el CONTENIDO binario de los archivos del sistema
// (avatares, fotos de expediente, documentos, cartas, recibos y comprobantes)
// directamente dentro de la base de datos.
//
// Motivo: las imágenes y documentos viven en storage/app/public (disco). Al
// mover/copiar el sistema a otro equipo o restaurar solo la BD, esos archivos
// no viajan y las fotos/documentos "no se ven". Esta tabla guarda una copia
// fiel del binario (codificado en base64) para poder reconstruir el disco con
// el comando `php artisan archivos:restaurar`.
//
// Se usa longText (base64) en lugar de BLOB para garantizar compatibilidad
// total entre MySQL/MariaDB y PostgreSQL y evitar problemas de escape tanto en
// mysqldump como en el respaldo PHP de BackupService.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos_bd', function (Blueprint $table) {
            $table->id();
            $table->string('ruta');                 // Ruta lógica (ej. expedientes/fotos/exp_12.jpg)
            $table->string('disco', 40)->default('public');
            $table->string('nombre_original')->nullable();
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('tamano')->nullable();  // Tamaño en bytes del binario
            $table->string('hash', 64)->nullable();            // sha256 del binario (verificación)
            $table->longText('contenido')->nullable();         // Binario codificado en base64
            $table->timestamps();

            $table->unique(['disco', 'ruta']);
            $table->index('hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos_bd');
    }
};
