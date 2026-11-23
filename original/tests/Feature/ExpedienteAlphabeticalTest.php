<?php

namespace Tests\Feature;

use App\Models\Expediente;
use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpedienteAlphabeticalTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_groups_expedientes_alphabetically_and_paginates_each_letter_by_ten(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        for ($i = 1; $i <= 11; $i++) {
            $this->crearExpediente('Ana ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'Apellido ' . $i);
        }
        $this->crearExpediente('Bruno', 'Apellido B');

        $this->actingAs($admin)
            ->getJson('/expedientes/letras')
            ->assertOk()
            ->assertJsonPath('letras.0.letra', 'A')
            ->assertJsonPath('letras.0.total', 11)
            ->assertJsonPath('letras.1.letra', 'B')
            ->assertJsonPath('letras.1.total', 1);

        $primeraPagina = $this->getJson('/expedientes?letra=A&per_page=10&page=1')
            ->assertOk()
            ->assertJsonPath('total', 11)
            ->assertJsonCount(10, 'data');

        $segundaPagina = $this->getJson('/expedientes?letra=A&per_page=10&page=2')
            ->assertOk()
            ->assertJsonPath('total', 11)
            ->assertJsonCount(1, 'data');
        $this->assertNotSame(
            $primeraPagina->json('data.9.id'),
            $segundaPagina->json('data.0.id')
        );
    }

    public function test_search_and_status_filters_are_applied_to_alphabetical_groups(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->crearExpediente('Ángela', 'Pérez', 100);
        $this->crearExpediente('Bruno', 'Gómez', 0);

        $this->actingAs($admin)
            ->getJson('/expedientes/letras?search=Ángela&estado=100')
            ->assertOk()
            ->assertJsonPath('letras.0.letra', 'A')
            ->assertJsonPath('letras.0.total', 1)
            ->assertJsonCount(1, 'letras');

        $this->getJson('/expedientes?letra=A&search=Ángela&estado=100&per_page=10')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.trabajador.nombres', 'Ángela');
    }

    private function crearExpediente(string $nombres, string $apellidos, int $estado = 0): Expediente
    {
        $trabajador = Trabajador::create([
            'cedula' => 'V-' . str_pad((string) (Trabajador::count() + 1), 8, '0', STR_PAD_LEFT),
            'nombres' => $nombres,
            'apellidos' => $apellidos,
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

        return Expediente::create([
            'trabajador_id' => $trabajador->id,
            'solicitud_id' => null,
            'estado_global' => $estado,
        ]);
    }
}
