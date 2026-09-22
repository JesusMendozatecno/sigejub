<?php
// Sembrador de la base de datos. Crea el usuario administrador por defecto (test@example.com).

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'nombre' => 'Test User',
            'correo' => 'test@example.com',
        ]);

        // Crear usuario superadmin si no existe
        if (!User::where('correo', 'superadmin@sigejub.com')->exists()) {
            User::create([
                'nombre' => 'Superadmin',
                'correo' => 'superadmin@sigejub.com',
                'password' => bcrypt('password'),
                'rol' => 'superadmin',
            ]);
        }

        $this->call(FormulaPrestacionSeeder::class);
    }
}
