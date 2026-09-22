<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN rol DROP DEFAULT");
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ALTER COLUMN rol TYPE VARCHAR(20)");
            DB::table('users')->where('rol', 'analista')->update(['rol' => 'usuario']);
            DB::statement("DROP TYPE IF EXISTS user_rol");
            DB::statement("CREATE TYPE user_rol AS ENUM('usuario','admin','superadmin')");
            DB::statement("ALTER TABLE users ALTER COLUMN rol TYPE user_rol USING rol::text::user_rol");
            DB::statement("ALTER TABLE users ALTER COLUMN rol SET DEFAULT 'usuario'");
        } elseif ($driver === 'sqlite') {
            // SQLite no soporta MODIFY/ENUM nativo: la columna queda como VARCHAR.
            DB::table('users')->where('rol', 'analista')->update(['rol' => 'usuario']);
        } else {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol VARCHAR(20) NOT NULL DEFAULT 'usuario'");
            DB::table('users')->where('rol', 'analista')->update(['rol' => 'usuario']);
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('usuario','admin','superadmin') NOT NULL DEFAULT 'usuario'");
        }

        if (!Schema::hasColumn('expedientes', 'carta_aprobacion')) {
            Schema::table('expedientes', function (Blueprint $table) {
                $table->string('carta_aprobacion')->nullable()->after('notas_admin');
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN rol DROP DEFAULT");
            DB::statement("DROP TYPE IF EXISTS user_rol");
            DB::statement("CREATE TYPE user_rol_old AS ENUM('admin','analista')");
            DB::statement("ALTER TABLE users ALTER COLUMN rol TYPE user_rol_old USING rol::text::user_rol_old");
            DB::statement("ALTER TABLE users ALTER COLUMN rol SET DEFAULT 'analista'");
            DB::statement("DROP TYPE IF EXISTS user_rol");
        } elseif ($driver === 'sqlite') {
            DB::table('users')->whereIn('rol', ['admin', 'superadmin'])->update(['rol' => 'analista']);
        } else {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin','analista') NOT NULL DEFAULT 'analista'");
        }

        if (Schema::hasColumn('expedientes', 'carta_aprobacion')) {
            Schema::table('expedientes', function (Blueprint $table) {
                $table->dropColumn('carta_aprobacion');
            });
        }
    }
};
