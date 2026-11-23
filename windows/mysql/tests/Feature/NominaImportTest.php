<?php

namespace Tests\Feature;

use App\Models\Nomina;
use App\Models\Trabajador;
use App\Models\User;
use App\Services\NominaImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class NominaImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_demo_workers_from_all_supported_sheets_and_avoids_duplicates(): void
    {
        $file = base_path('documentos/nomina_ejemplo_importacion.xlsx');
        $service = new NominaImportService();

        $firstImport = $service->importar($file, '2098', 2);

        $this->assertSame(4, $firstImport['encontrados']);
        $this->assertSame(4, $firstImport['registrados']);
        $this->assertSame(4, $firstImport['nuevos']);
        $this->assertSame([], $firstImport['errores']);
        $this->assertDatabaseCount('trabajadores', 4);
        $this->assertDatabaseCount('nomina_trabajador', 4);
        $this->assertDatabaseHas('trabajadores', [
            'cedula' => 'V-99990001',
            'genero' => 'M',
            'tipo_nomina' => 'ADM',
        ]);
        $this->assertDatabaseHas('trabajadores', [
            'cedula' => 'V-99990002',
            'genero' => 'F',
            'tipo_nomina' => 'DOC',
        ]);
        $this->assertDatabaseHas('trabajadores', [
            'cedula' => 'V-99990003',
            'genero' => 'M',
            'tipo_nomina' => 'OBREROS',
            'grado_nivel' => 'P2',
            'grado_cargo' => 'P2',
        ]);

        $nomina = Nomina::where('periodo', '2098')->where('mes', 2)->firstOrFail();
        $this->assertEquals(900, (float) $nomina->total_general);

        $secondImport = $service->importar($file, '2098', 2);

        $this->assertSame(4, $secondImport['ya_existentes']);
        $this->assertSame(0, $secondImport['nuevos']);
        $this->assertDatabaseCount('trabajadores', 4);
        $this->assertDatabaseCount('nomina_trabajador', 4);
    }

    public function test_the_upload_endpoint_imports_the_demo_workbook(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'nomina-');
        $this->assertNotFalse($source);
        copy(base_path('documentos/nomina_ejemplo_importacion.xlsx'), $source);

        try {
            $upload = new UploadedFile(
                $source,
                'nomina_ejemplo_importacion.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                UPLOAD_ERR_OK,
                true
            );

            $response = $this->actingAs(User::factory()->create(['rol' => 'admin']))
                ->post('/importar/nomina', [
                    'archivo' => $upload,
                    'anio' => '2098',
                    'mes' => '2',
                ]);

            $response->assertOk()
                ->assertJsonPath('estado', 'success')
                ->assertJsonPath('datos.registrados', 4)
                ->assertJsonPath('datos.errores', []);
            $datos = $response->json('datos');
            $mensaje = $response->json('mensaje');
            $this->assertStringContainsString('Trabajadores encontrados: ' . $datos['encontrados'], $mensaje);
            $this->assertStringContainsString('Trabajadores nuevos registrados: ' . $datos['nuevos'], $mensaje);
            $this->assertStringContainsString('Trabajadores omitidos: ' . $datos['omitidos'], $mensaje);
            $this->assertStringNotContainsString('Trabajadores ya registrados', $mensaje);
            $this->assertStringNotContainsString('ya se encuentran registrados', $mensaje);
            $this->assertDatabaseCount('trabajadores', 4);
            $this->assertDatabaseCount('nomina_trabajador', 4);
        } finally {
            if (file_exists($source)) {
                unlink($source);
            }
        }
    }

    public function test_years_are_listed_by_year_in_descending_order(): void
    {
        foreach (['2000', '2026', '2010'] as $anio) {
            Nomina::create([
                'codigo' => 'NOM-' . $anio,
                'periodo' => $anio,
                'mes' => null,
                'estado' => 'borrador',
                'total_general' => 0,
            ]);
        }

        $response = $this->actingAs(User::factory()->create(['rol' => 'admin']))
            ->get('/nomina/anios');

        $response->assertOk()
            ->assertJsonPath('anios.0.anio', 2026)
            ->assertJsonPath('anios.1.anio', 2010)
            ->assertJsonPath('anios.2.anio', 2000);
    }
}
