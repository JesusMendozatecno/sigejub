<?php
// Seeder: Corregir y estandarizar la columna ENUM rol en la tabla users.
// Convierte temporalmente el ENUM a VARCHAR, corrige valores inválidos
// (analista → usuario), y re-aplica el ENUM con los valores correctos:
// ('usuario', 'admin', 'superadmin').

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixRolesSeeder extends Seeder
{
    public function run(): void
    {
        $driver = DB::getDriverName();

        $this->command->info('Checking current rol values...');
        $rows = DB::select('SELECT DISTINCT rol FROM users');
        foreach ($rows as $r) {
            $this->command->info("  Found value: [{$r->rol}]");
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $this->command->info('Converting rol column to VARCHAR...');
            DB::unprepared("ALTER TABLE users MODIFY COLUMN rol VARCHAR(20) NOT NULL DEFAULT 'usuario'");
            $this->command->info('Done.');
        }

        $this->command->info('Updating invalid values...');
        $count = DB::table('users')->where('rol', '!=', 'admin')->where('rol', '!=', 'superadmin')->where('rol', '!=', 'usuario')->update(['rol' => 'usuario']);
        $this->command->info("Updated {$count} rows.");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $this->command->info('Setting ENUM constraint...');
            DB::unprepared("ALTER TABLE users MODIFY COLUMN rol ENUM('usuario','admin','superadmin') NOT NULL DEFAULT 'usuario'");
            $this->command->info('Done.');
        }

        $rows = DB::select('SELECT id, nombre, rol FROM users');
        foreach ($rows as $r) {
            $this->command->info("  User {$r->id}: {$r->nombre} -> [{$r->rol}]");
        }
    }
}
