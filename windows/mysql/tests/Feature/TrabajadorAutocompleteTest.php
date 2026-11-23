<?php

namespace Tests\Feature;

use App\Models\Trabajador;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrabajadorAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_solicitud_autocomplete_searches_by_cedula_with_or_without_hyphen(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'admin']));
        $trabajador = $this->crearTrabajador('V-28253235', 'Ana', 'Pérez');

        $this->getJson('/trabajadores/autocomplete?search=v28253235')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.cedula', 'V-28253235');

        $this->getJson('/trabajadores/autocomplete?search=V-28253235')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.cedula', 'V-28253235');

        $this->getJson('/trabajadores/autocomplete?search=Ana')
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_expediente_autocomplete_includes_all_workers_and_sorts_alphabetically(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'admin']));
        $trabajadorConSolicitud = $this->crearTrabajador('V-10000001', 'Ana', 'Pérez');
        $this->crearTrabajador('V-10000002', 'Zulema', 'Rojas');

        Solicitud::create([
            'trabajador_id' => $trabajadorConSolicitud->id,
            'fecha_solicitud' => '2026-01-01',
            'estado' => 'pendiente',
        ]);

        $this->getJson('/trabajadores/autocomplete?search=V-&incluir_con_solicitud=1')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.nombres', 'Ana')
            ->assertJsonPath('1.nombres', 'Zulema');

        $this->getJson('/trabajadores/autocomplete?search=V-')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.nombres', 'Zulema');
    }

    private function crearTrabajador(string $cedula, string $nombres, string $apellidos): Trabajador
    {
        return Trabajador::create([
            'cedula' => $cedula,
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
    }
}
