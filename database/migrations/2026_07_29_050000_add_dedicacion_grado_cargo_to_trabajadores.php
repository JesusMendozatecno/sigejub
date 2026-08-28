<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->string('dedicacion', 100)->nullable()->after('nivel_educativo_texto');
            $table->string('grado_cargo', 100)->nullable()->after('dedicacion');
        });
    }

    public function down(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->dropColumn('dedicacion');
            $table->dropColumn('grado_cargo');
        });
    }
};
