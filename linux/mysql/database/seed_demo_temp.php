<?php
// Boot temporal de Laravel para sembrar datos demo (se elimina tras usar).
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Trabajador;
use App\Models\User;
use App\Models\Solicitud;
use App\Models\Cargo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$cargos = ['Analista', 'Coordinador', 'Supervisor', 'Tecnico', 'Jefe de Unidad', 'Asistente', 'Administrativo', 'Ingeniero'];
foreach ($cargos as $i => $c) {
    if (!Cargo::where('nombre', $c)->exists()) Cargo::create(['nombre' => $c, 'codigo' => 'C' . str_pad($i + 1, 3, '0', STR_PAD_LEFT)]);
}

$nombres = [
    ['Maria', 'Gonzalez', 'F'], ['Jose', 'Perez', 'M'], ['Carmen', 'Rodriguez', 'F'],
    ['Luis', 'Martinez', 'M'], ['Ana', 'Fernandez', 'F'], ['Pedro', 'Lopez', 'M'],
    ['Rosa', 'Sanchez', 'F'], ['Carlos', 'Garcia', 'M'], ['Laura', 'Diaz', 'F'], ['Jorge', 'Moreno', 'M'],
];

$tips = ['jubilacion por edad', 'jubilacion por anos de servicio', 'jubilacion especial'];
$estados = ['pendiente', 'revision', 'aprobado', 'rechazado'];
$unidades = ['Informatica', 'RRHH', 'Contabilidad', 'Operaciones', 'Legal'];

DB::transaction(function () use ($nombres, $cargos, $tips, $estados, $unidades) {
    for ($i = 0; $i < 26; $i++) {
        list($nom, $ape, $g) = $nombres[array_rand($nombres)];
        $nac = Carbon::today()->subYears(rand(50, 68))->subMonths(rand(0, 11));
        $ing = Carbon::today()->subYears(rand(10, 40))->subMonths(rand(0, 11));
        $ant = (int) $ing->diffInYears(now());
        $ext = rand(0, 15);
        $trab = Trabajador::create([
            'cedula' => 'V-' . rand(1000000, 25000000),
            'nombres' => $nom . ' ' . $nom,
            'apellidos' => $ape . ' ' . $ape,
            'genero' => $g,
            'grado_nivel' => 'G' . rand(1, 5) . '-N' . rand(1, 3),
            'cargo' => $cargos[array_rand($cargos)],
            'unidad_departamento' => $unidades[array_rand($unidades)],
            'fecha_nacimiento' => $nac->toDateString(),
            'edad' => $nac->age,
            'fecha_ingreso' => $ing->toDateString(),
            'anos_servicio_inst' => $ant,
            'anos_servicio_externo' => $ext,
            'total_anos_servicio' => $ant + $ext,
            'nivel_instruccion' => rand(1, 5),
            'numero_hijos' => rand(0, 4),
            'hijos_discapacidad' => 0,
        ]);

        Solicitud::create([
            'trabajador_id' => $trab->id,
            'fecha_solicitud' => Carbon::today()->subMonths(rand(0, 5))->subDays(rand(0, 25))->toDateString(),
            'tipo_jubilacion' => $tips[array_rand($tips)],
            'estado' => $estados[array_rand($estados)],
            'aprobada' => null,
        ]);
    }
});

if (!User::where('correo', 'admin@sigejub.com')->exists()) {
    User::create(['nombre' => 'Administrador', 'correo' => 'admin@sigejub.com', 'password' => bcrypt('password'), 'rol' => 'admin']);
}

echo 'Trabajadores: ' . Trabajador::count() . PHP_EOL;
echo 'Solicitudes: ' . Solicitud::count() . PHP_EOL;
echo 'Cargos: ' . Cargo::count() . PHP_EOL;
echo 'Usuarios: ' . User::count() . PHP_EOL;
