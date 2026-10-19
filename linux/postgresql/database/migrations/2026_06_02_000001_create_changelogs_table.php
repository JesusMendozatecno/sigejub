<?php
// Migración para crear la tabla changelogs para registrar cambios de git.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelogs', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_autor');
            $table->string('correo_autor');
            $table->string('hash_commit', 40)->unique();
            $table->text('mensaje_commit');
            $table->text('descripcion')->nullable();
            $table->string('tipo')->default('change');
            $table->string('seccion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelogs');
    }
};
