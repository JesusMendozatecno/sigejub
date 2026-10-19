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
        // Usuario de pruebas: solo si SEED_TEST_USER=true (entornos de desarrollo).
        if (filter_var(env('SEED_TEST_USER', false), FILTER_VALIDATE_BOOL)
            && !User::where('correo', 'test@example.com')->exists()) {
            User::factory()->create([
                'nombre' => 'Test User',
                'correo' => 'test@example.com',
            ]);
        }

        // Catálogos maestros del sistema (idempotente).
        $this->call(MasterDataSeeder::class);

        $this->call(FormulaPrestacionSeeder::class);

        // Crear usuario superadmin si no existe
        if (!User::where('correo', 'superadmin@sigejub.com')->exists()) {
            $admin = User::create([
                'nombre' => 'Superadmin',
                'correo' => 'superadmin@sigejub.com',
                'password' => bcrypt('password'),
                'rol' => 'superadmin',
            ]);

            // Imagen de perfil por defecto (avatars/u_{id}.png).
            $rutaAvatar = app(\App\Services\ImageStorageService::class)
                ->copiarPorDefecto('avatars', 'u_' . $admin->id, public_path('img/avatar-default.png'));
            if ($rutaAvatar) {
                $admin->avatar = $rutaAvatar;
                $admin->save();
            }
        }
    }
}
