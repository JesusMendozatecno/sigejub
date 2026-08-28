<?php
// Migración para añadir el selector de tipografía del usuario a la tabla users.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'tipografia')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('tipografia', 30)->default('sistema')->after('color_acento');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'tipografia')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('tipografia');
            });
        }
    }
};
