<?php
// Migración: renombra columnas de users/activities/notifications al español.
// Portable: usa el Schema Builder (MySQL/MariaDB, PostgreSQL y SQLite).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function renameColumn(string $table, string $old, string $new): void
    {
        if (!Schema::hasColumn($table, $old) || Schema::hasColumn($table, $new)) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            // En MySQL/MariaDB se reconstruye la definición leyéndola de SHOW FULL
            // COLUMNS. No se usa el generador de Laravel porque MariaDB devuelve los
            // defaults de texto entre comillas en information_schema y Laravel los
            // reinyecta como default '''valor''', lo que rompe los ENUM con el
            // error 1067 (Invalid default value) al instalar desde cero.
            $col = null;
            foreach (DB::select("SHOW FULL COLUMNS FROM {$this->wrap($table)}") as $candidate) {
                if (($candidate->Field ?? null) === $old) {
                    $col = $candidate;
                    break;
                }
            }
            if ($col) {
                $def = $col->Type;
                if (!empty($col->Collation)) {
                    $def .= ' COLLATE ' . $col->Collation;
                }
                $def .= ($col->Null === 'NO') ? ' NOT NULL' : ' NULL';

                $default = $col->Default;
                $extra = strtolower((string) $col->Extra);
                if ($default !== null) {
                    $isExpression = str_contains($extra, 'default_generated')
                        || preg_match('/^(current_timestamp|now\(\))/i', (string) $default) === 1;
                    if ($isExpression || (is_numeric($default) && preg_match('/^0\d/', (string) $default) !== 1)) {
                        $def .= ' DEFAULT ' . $default;
                    } else {
                        $def .= " DEFAULT '" . str_replace("'", "''", (string) $default) . "'";
                    }
                }

                if (str_contains($extra, 'auto_increment')) {
                    $def .= ' AUTO_INCREMENT';
                }
                if (!empty($col->Comment)) {
                    $def .= " COMMENT '" . str_replace("'", "''", $col->Comment) . "'";
                }

                DB::statement("ALTER TABLE {$this->wrap($table)} CHANGE {$this->wrap($old)} {$this->wrap($new)} {$def}");
                return;
            }
        }

        Schema::table($table, function (Blueprint $t) use ($old, $new) {
            $t->renameColumn($old, $new);
        });
    }

    private function wrap(string $identifier): string
    {
        return DB::connection()->getQueryGrammar()->wrap($identifier);
    }

    private function dropIndex(string $table, string $index): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        foreach (Schema::getIndexes($table) as $idx) {
            if (($idx['name'] ?? '') === $index) {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex($index));
                return;
            }
        }
    }

    private function createIndex(string $table, string $index, string $column): void
    {
        Schema::table($table, fn (Blueprint $t) => $t->index($column, $index));
    }

    public function up(): void
    {
        $userRenames = [
            'name'                 => 'nombre',
            'email'                => 'correo',
            'surname'              => 'apellido',
            'phone'                => 'telefono',
            'role'                 => 'rol',
            'theme'                => 'tema',
            'language'             => 'idioma',
            'accent_color'         => 'color_acento',
            'two_factor_enabled'   => 'verificacion_dos_pasos',
            'two_factor_secret'    => 'secreto_2fa',
            'notification_email'   => 'notificacion_correo',
            'notification_system'  => 'notificacion_sistema',
            'profile_public'       => 'perfil_publico',
            'last_login_at'        => 'ultimo_acceso',
            'last_login_ip'        => 'ultimo_acceso_ip',
            'remember_token'       => 'token_recordar',
            'email_verified_at'    => 'correo_verificado_en',
        ];

        foreach ($userRenames as $old => $new) {
            $this->renameColumn('users', $old, $new);
        }

        // activities
        $this->renameColumn('activities', 'action', 'accion');
        $this->renameColumn('activities', 'subject_type', 'tipo_entidad');
        $this->renameColumn('activities', 'subject_id', 'entidad_id');
        $this->renameColumn('activities', 'description', 'descripcion');
        $this->renameColumn('activities', 'ip_address', 'direccion_ip');
        $this->renameColumn('activities', 'user_agent', 'navegador');
        $this->renameColumn('activities', 'old_values', 'valores_anteriores');
        $this->renameColumn('activities', 'new_values', 'valores_nuevos');
        $this->renameColumn('activities', 'request_data', 'datos_peticion');

        if (Schema::hasTable('activities')) {
            $this->dropIndex('activities', 'activities_action_index');
            $this->dropIndex('activities', 'activities_subject_type_index');
            $this->createIndex('activities', 'ix_accion', 'accion');
            $this->createIndex('activities', 'ix_tipo_entidad', 'tipo_entidad');
        }

        // notifications
        $this->renameColumn('notifications', 'title', 'titulo');
        $this->renameColumn('notifications', 'message', 'mensaje');
        $this->renameColumn('notifications', 'type', 'tipo');
        $this->renameColumn('notifications', 'is_read', 'leida');
    }

    public function down(): void
    {
        $userRenames = [
            'nombre'               => 'name',
            'correo'               => 'email',
            'apellido'             => 'surname',
            'telefono'             => 'phone',
            'rol'                  => 'role',
            'tema'                 => 'theme',
            'idioma'               => 'language',
            'color_acento'         => 'accent_color',
            'verificacion_dos_pasos' => 'two_factor_enabled',
            'secreto_2fa'          => 'two_factor_secret',
            'notificacion_correo'  => 'notification_email',
            'notificacion_sistema' => 'notification_system',
            'perfil_publico'       => 'profile_public',
            'ultimo_acceso'        => 'last_login_at',
            'ultimo_acceso_ip'     => 'last_login_ip',
            'token_recordar'       => 'remember_token',
            'correo_verificado_en' => 'email_verified_at',
        ];

        foreach ($userRenames as $old => $new) {
            $this->renameColumn('users', $old, $new);
        }

        $this->renameColumn('activities', 'accion', 'action');
        $this->renameColumn('activities', 'tipo_entidad', 'subject_type');
        $this->renameColumn('activities', 'entidad_id', 'subject_id');
        $this->renameColumn('activities', 'descripcion', 'description');
        $this->renameColumn('activities', 'direccion_ip', 'ip_address');
        $this->renameColumn('activities', 'navegador', 'user_agent');
        $this->renameColumn('activities', 'valores_anteriores', 'old_values');
        $this->renameColumn('activities', 'valores_nuevos', 'new_values');
        $this->renameColumn('activities', 'datos_peticion', 'request_data');

        if (Schema::hasTable('activities')) {
            $this->dropIndex('activities', 'ix_accion');
            $this->dropIndex('activities', 'ix_tipo_entidad');
        }

        $this->renameColumn('notifications', 'titulo', 'title');
        $this->renameColumn('notifications', 'mensaje', 'message');
        $this->renameColumn('notifications', 'tipo', 'type');
        $this->renameColumn('notifications', 'leida', 'is_read');
    }
};
