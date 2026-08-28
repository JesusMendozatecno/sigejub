<?php
// Migración: elimina tablas que ya no usa el sistema.
// Nota: NO se eliminan changelogs (la usa el módulo Documentación)
// ni jobs/failed_jobs/job_batches (QUEUE_CONNECTION=database las necesita).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (['prestaciones_sociales'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // No recovery needed; these are empty or non-critical system tables.
    }
};
