<?php
// Migración: Índices de rendimiento para consultas frecuentes.
// Agrega índices en columnas que se usan en filtros, ORDER BY y JOINs
// de las tablas más consultadas: solicitudes, expedientes, documentos,
// activities, notifications, nominas, prestaciones y prestaciones_sociales.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addIndexIfMissing(string $table, string $column): void
    {
        $indexName = "{$table}_{$column}_index";
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $exists = DB::select(
                "SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                [$table, $indexName]
            );
        } else {
            // Portable: MySQL/MariaDB/SQLite vía Schema Builder
            $exists = collect(Schema::getIndexes($table))
                ->first(fn ($idx) => ($idx['name'] ?? '') === $indexName);
        }

        if (empty($exists)) {
            Schema::table($table, fn (Blueprint $t) => $t->index($column));
        }
    }

    public function up(): void
    {
        $this->addIndexIfMissing('solicitudes', 'estado');
        $this->addIndexIfMissing('solicitudes', 'trabajador_id');
        $this->addIndexIfMissing('solicitudes', 'created_at');

        $this->addIndexIfMissing('expedientes', 'trabajador_id');
        $this->addIndexIfMissing('expedientes', 'solicitud_id');
        $this->addIndexIfMissing('expedientes', 'estado_global');

        $this->addIndexIfMissing('documentos', 'expediente_id');
        $this->addIndexIfMissing('documentos', 'estado');

        $this->addIndexIfMissing('activities', 'user_id');
        $this->addIndexIfMissing('activities', 'tipo_entidad');
        $this->addIndexIfMissing('activities', 'created_at');

        $this->addIndexIfMissing('notifications', 'user_id');
        $this->addIndexIfMissing('notifications', 'leida');

        $this->addIndexIfMissing('nominas', 'trabajador_id');

        $this->addIndexIfMissing('prestaciones', 'trabajador_id');

        $this->addIndexIfMissing('prestaciones_sociales', 'trabajador_id');
    }

    public function down(): void
    {
        $drops = [
            'solicitudes'            => ['estado', 'trabajador_id', 'created_at'],
            'expedientes'            => ['trabajador_id', 'solicitud_id', 'estado_global'],
            'documentos'             => ['expediente_id', 'estado'],
            'activities'             => ['user_id', 'tipo_entidad', 'created_at'],
            'notifications'          => ['user_id', 'leida'],
            'nominas'                => ['trabajador_id'],
            'prestaciones'           => ['trabajador_id'],
        ];

        foreach ($drops as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex($columns));
            }
        }
    }
};
