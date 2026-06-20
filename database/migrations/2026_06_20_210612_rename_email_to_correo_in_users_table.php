<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('email', 'correo');
            $table->renameColumn('email_verified_at', 'correo_verificado_en');
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('role', 'rol');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('correo', 'email');
            $table->renameColumn('correo_verificado_en', 'email_verified_at');
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('rol', 'role');
        });
    }
};
