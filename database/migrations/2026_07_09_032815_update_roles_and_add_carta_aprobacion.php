<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('usuario','admin','superadmin') NOT NULL DEFAULT 'usuario'");

        Schema::table('expedientes', function (Blueprint $table) {
            $table->string('carta_aprobacion')->nullable()->after('notas_admin');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin','analista') NOT NULL DEFAULT 'analista'");

        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropColumn('carta_aprobacion');
        });
    }
};
