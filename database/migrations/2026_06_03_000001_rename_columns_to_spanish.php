<?php
// Migración para renombrar columnas de users, activities, notifications y changelogs al español.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check which columns still need renaming in users table
        $userCols = DB::select('SHOW COLUMNS FROM users');
        $userColNames = array_column($userCols, 'Field');

        $userRenames = [
            'name'            => ['nombre',             "VARCHAR(255) NOT NULL"],
            'email'           => ['correo',             "VARCHAR(255) NOT NULL"],
            'surname'         => ['apellido',           "VARCHAR(255) DEFAULT NULL"],
            'phone'           => ['telefono',           "VARCHAR(255) DEFAULT NULL"],
            'role'            => ['rol',               "ENUM('admin','analista') NOT NULL DEFAULT 'analista'"],
            'theme'           => ['tema',               "VARCHAR(255) NOT NULL DEFAULT 'light'"],
            'language'        => ['idioma',             "VARCHAR(255) NOT NULL DEFAULT 'es'"],
            'accent_color'    => ['color_acento',       "VARCHAR(255) NOT NULL DEFAULT '#1a365d'"],
            'two_factor_enabled' => ['verificacion_dos_pasos', "TINYINT(1) NOT NULL DEFAULT 0"],
            'two_factor_secret'  => ['secreto_2fa',     "VARCHAR(255) DEFAULT NULL"],
            'notification_email' => ['notificacion_correo', "VARCHAR(255) NOT NULL DEFAULT 'all'"],
            'notification_system' => ['notificacion_sistema', "VARCHAR(255) NOT NULL DEFAULT 'all'"],
            'profile_public'  => ['perfil_publico',     "TINYINT(1) NOT NULL DEFAULT 1"],
            'last_login_at'   => ['ultimo_acceso',      "DATETIME DEFAULT NULL"],
            'last_login_ip'   => ['ultimo_acceso_ip',   "VARCHAR(255) DEFAULT NULL"],
            'remember_token'  => ['token_recordar',     "VARCHAR(100) DEFAULT NULL"],
            'email_verified_at' => ['correo_verificado_en', "DATETIME DEFAULT NULL"],
        ];

        foreach ($userRenames as $old => [$new, $type]) {
            if (in_array($old, $userColNames)) {
                DB::statement("ALTER TABLE users CHANGE `{$old}` `{$new}` {$type}");
            }
        }

        // ─── activities ───
        DB::statement("ALTER TABLE activities CHANGE `action` `accion` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE activities CHANGE `subject_type` `tipo_entidad` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE activities CHANGE `subject_id` `entidad_id` BIGINT UNSIGNED DEFAULT NULL");
        DB::statement("ALTER TABLE activities CHANGE `description` `descripcion` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE activities CHANGE `ip_address` `direccion_ip` VARCHAR(45) DEFAULT NULL");
        DB::statement("ALTER TABLE activities CHANGE `user_agent` `navegador` TEXT DEFAULT NULL");
        DB::statement("ALTER TABLE activities CHANGE `old_values` `valores_anteriores` LONGTEXT DEFAULT NULL");
        DB::statement("ALTER TABLE activities CHANGE `new_values` `valores_nuevos` LONGTEXT DEFAULT NULL");
        DB::statement("ALTER TABLE activities CHANGE `request_data` `datos_peticion` LONGTEXT DEFAULT NULL");

        // NOTE: `sessions.last_activity` is intentionally NOT renamed.
        // Laravel's DatabaseSessionHandler hardcodes `last_activity` as the column name.
        // Renaming it would break the session write path.

        // Rebuild indexes for renamed activity columns
        Schema::table('activities', function ($table) {
            $table->dropIndex(['action']);
            $table->dropIndex(['subject_type']);
        });
        DB::statement('ALTER TABLE activities ADD INDEX ix_accion (accion)');
        DB::statement('ALTER TABLE activities ADD INDEX ix_tipo_entidad (tipo_entidad)');

        // ─── notifications ───
        DB::statement("ALTER TABLE notifications CHANGE `title` `titulo` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE notifications CHANGE `message` `mensaje` TEXT NOT NULL");
        DB::statement("ALTER TABLE notifications CHANGE `type` `tipo` VARCHAR(255) NOT NULL DEFAULT 'info'");
        DB::statement("ALTER TABLE notifications CHANGE `is_read` `leida` TINYINT(1) NOT NULL DEFAULT 0");

        // ─── changelogs ───
        DB::statement("ALTER TABLE changelogs CHANGE `author_name` `nombre_autor` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE changelogs CHANGE `author_email` `correo_autor` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE changelogs CHANGE `commit_hash` `hash_commit` VARCHAR(40) NOT NULL");
        DB::statement("ALTER TABLE changelogs CHANGE `commit_message` `mensaje_commit` TEXT NOT NULL");
        DB::statement("ALTER TABLE changelogs CHANGE `description` `descripcion` TEXT DEFAULT NULL");
        DB::statement("ALTER TABLE changelogs CHANGE `type` `tipo` VARCHAR(255) NOT NULL DEFAULT 'change'");
        DB::statement("ALTER TABLE changelogs CHANGE `section` `seccion` VARCHAR(255) DEFAULT NULL");
    }

    public function down(): void
    {
        // Check which columns currently exist
        $userCols = DB::select('SHOW COLUMNS FROM users');
        $userColNames = array_column($userCols, 'Field');

        $userRenames = [
            'nombre'              => ['name',               "VARCHAR(255) NOT NULL"],
            'correo'              => ['email',              "VARCHAR(255) NOT NULL"],
            'apellido'            => ['surname',            "VARCHAR(255) DEFAULT NULL"],
            'telefono'            => ['phone',              "VARCHAR(255) DEFAULT NULL"],
            'rol'                 => ['role',               "ENUM('admin','analista') NOT NULL DEFAULT 'analista'"],
            'tema'                => ['theme',              "VARCHAR(255) NOT NULL DEFAULT 'light'"],
            'idioma'              => ['language',           "VARCHAR(255) NOT NULL DEFAULT 'es'"],
            'color_acento'        => ['accent_color',       "VARCHAR(255) NOT NULL DEFAULT '#1a365d'"],
            'verificacion_dos_pasos' => ['two_factor_enabled', "TINYINT(1) NOT NULL DEFAULT 0"],
            'secreto_2fa'         => ['two_factor_secret',  "VARCHAR(255) DEFAULT NULL"],
            'notificacion_correo' => ['notification_email', "VARCHAR(255) NOT NULL DEFAULT 'all'"],
            'notificacion_sistema' => ['notification_system', "VARCHAR(255) NOT NULL DEFAULT 'all'"],
            'perfil_publico'      => ['profile_public',     "TINYINT(1) NOT NULL DEFAULT 1"],
            'ultimo_acceso'       => ['last_login_at',      "DATETIME DEFAULT NULL"],
            'ultimo_acceso_ip'    => ['last_login_ip',      "VARCHAR(255) DEFAULT NULL"],
            'token_recordar'      => ['remember_token',     "VARCHAR(100) DEFAULT NULL"],
            'correo_verificado_en' => ['email_verified_at', "DATETIME DEFAULT NULL"],
        ];

        foreach ($userRenames as $new => [$old, $type]) {
            if (in_array($new, $userColNames)) {
                DB::statement("ALTER TABLE users CHANGE `{$new}` `{$old}` {$type}");
            }
        }

        // NOTE: `sessions.last_activity` is intentionally not reverted; see up() for rationale.

        // ─── activities ───
        DB::statement("ALTER TABLE activities CHANGE `accion` `action` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE activities CHANGE `tipo_entidad` `subject_type` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE activities CHANGE `entidad_id` `subject_id` BIGINT UNSIGNED DEFAULT NULL");
        DB::statement("ALTER TABLE activities CHANGE `descripcion` `description` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE activities CHANGE `direccion_ip` `ip_address` VARCHAR(45) DEFAULT NULL");
        DB::statement("ALTER TABLE activities CHANGE `navegador` `user_agent` TEXT DEFAULT NULL");
        DB::statement("ALTER TABLE activities CHANGE `valores_anteriores` `old_values` LONGTEXT DEFAULT NULL");
        DB::statement("ALTER TABLE activities CHANGE `valores_nuevos` `new_values` LONGTEXT DEFAULT NULL");
        DB::statement("ALTER TABLE activities CHANGE `datos_peticion` `request_data` LONGTEXT DEFAULT NULL");

        Schema::table('activities', function ($table) {
            $table->dropIndex('ix_accion');
            $table->dropIndex('ix_tipo_entidad');
        });

        // ─── notifications ───
        DB::statement("ALTER TABLE notifications CHANGE `titulo` `title` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE notifications CHANGE `mensaje` `message` TEXT NOT NULL");
        DB::statement("ALTER TABLE notifications CHANGE `tipo` `type` VARCHAR(255) NOT NULL DEFAULT 'info'");
        DB::statement("ALTER TABLE notifications CHANGE `leida` `is_read` TINYINT(1) NOT NULL DEFAULT 0");

        // ─── changelogs ───
        DB::statement("ALTER TABLE changelogs CHANGE `nombre_autor` `author_name` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE changelogs CHANGE `correo_autor` `author_email` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE changelogs CHANGE `hash_commit` `commit_hash` VARCHAR(40) NOT NULL");
        DB::statement("ALTER TABLE changelogs CHANGE `mensaje_commit` `commit_message` TEXT NOT NULL");
        DB::statement("ALTER TABLE changelogs CHANGE `descripcion` `description` TEXT DEFAULT NULL");
        DB::statement("ALTER TABLE changelogs CHANGE `tipo` `type` VARCHAR(255) NOT NULL DEFAULT 'change'");
        DB::statement("ALTER TABLE changelogs CHANGE `seccion` `section` VARCHAR(255) DEFAULT NULL");
    }
};
