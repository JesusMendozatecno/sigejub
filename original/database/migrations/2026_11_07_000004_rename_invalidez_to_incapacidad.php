<?php
// Migración: la modalidad de jubilación "Invalidez" pasa a llamarse "Incapacidad"
// (denominación usada por la institución). Se actualiza el catálogo y las
// solicitudes ya registradas para que no queden dos nombres distintos.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tipos_jubilacion')
            ->where('nombre', 'Invalidez')
            ->update(['nombre' => 'Incapacidad', 'codigo' => 'INC']);

        DB::table('solicitudes')
            ->where('tipo_jubilacion', 'Invalidez')
            ->update(['tipo_jubilacion' => 'Incapacidad']);
    }

    public function down(): void
    {
        DB::table('tipos_jubilacion')
            ->where('nombre', 'Incapacidad')
            ->update(['nombre' => 'Invalidez', 'codigo' => 'INV']);

        DB::table('solicitudes')
            ->where('tipo_jubilacion', 'Incapacidad')
            ->update(['tipo_jubilacion' => 'Invalidez']);
    }
};
