<?php

namespace Tests\Feature;

use App\Models\Expediente;
use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrestacionesAlphabeticalTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_groups_eligible_workers_alphabetically_and_paginates_each_letter_by_ten(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'admin']));
        for ($i = 1; $i <= 11; $i++) {
            $trabajador = Trabajador::create([
                'cedula' => 'V-' . str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                'nombres' => 'Ana',
                'apellidos' => 'Apellido ' . $i,
                'genero' => 'F',
                'grado_nivel' => 'P1',
                'cargo' => 'Docente',
                'unidad_departamento' => 'Administración',
                'fecha_nacimiento' => '1980-01-01',
                'edad' => 46,
                'fecha_ingreso' => '2010-01-01',
                'anos_servicio_inst' => 16,
                'anos_servicio_externo' => 0,
                'total_anos_servicio' => 16,
                'nivel_instruccion' => 1,
                'numero_hijos' => 0,
            ]);

            Expediente::create([
                'trabajador_id' => $trabajador->id,
                'solicitud_id' => null,
                'estado_global' => 100,
            ]);
        }

        $this->getJson('/prestaciones/letras?search=Ana')
            ->assertOk()
            ->assertJsonPath('letras.0.letra', 'A')
            ->assertJsonPath('letras.0.total', 11);

        $primeraPagina = $this->getJson('/prestaciones?search=Ana&letra=A&per_page=10&page=1')
            ->assertOk()
            ->assertJsonPath('total', 11)
            ->assertJsonCount(10, 'data');

        $segundaPagina = $this->getJson('/prestaciones?search=Ana&letra=A&per_page=10&page=2')
            ->assertOk()
            ->assertJsonPath('total', 11)
            ->assertJsonCount(1, 'data');

        $this->assertNotSame(
            $primeraPagina->json('data.9.id'),
            $segundaPagina->json('data.0.id')
        );
    }
}
