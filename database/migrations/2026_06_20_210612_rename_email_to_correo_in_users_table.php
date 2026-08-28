<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasColumn(string $table, string $column): bool
    {
        if (DB::getDriverName() === 'pgsql') {
            return !empty(DB::select(
                "SELECT column_name FROM information_schema.columns WHERE table_name = ? AND column_name = ?",
                [$table, $column]
            ));
        }
        return Schema::hasColumn($table, $column);
    }

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->hasColumn('users', 'email')) {
                $table->renameColumn('email', 'correo');
            }
            if ($this->hasColumn('users', 'email_verified_at')) {
                $table->renameColumn('email_verified_at', 'correo_verificado_en');
            }
            if ($this->hasColumn('users', 'name')) {
                $table->renameColumn('name', 'nombre');
            }
            if ($this->hasColumn('users', 'role')) {
                $table->renameColumn('role', 'rol');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->hasColumn('users', 'correo')) {
                $table->renameColumn('correo', 'email');
            }
            if ($this->hasColumn('users', 'correo_verificado_en')) {
                $table->renameColumn('correo_verificado_en', 'email_verified_at');
            }
            if ($this->hasColumn('users', 'nombre')) {
                $table->renameColumn('nombre', 'name');
            }
            if ($this->hasColumn('users', 'rol')) {
                $table->renameColumn('rol', 'role');
            }
        });
    }
};
