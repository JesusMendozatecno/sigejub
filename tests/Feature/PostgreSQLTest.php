<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostgreSQLTest extends TestCase
{
    public function test_database_driver_is_pgsql(): void
    {
        $driver = DB::connection()->getDriverName();
        $this->assertEquals('pgsql', $driver, "La conexión debe ser PostgreSQL");
    }

    public function test_connection_is_alive(): void
    {
        $result = DB::select('SELECT 1 as alive');
        $this->assertEquals(1, $result[0]->alive);
    }

    public function test_users_table_exists(): void
    {
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('users'));
    }

    public function test_changelogs_table_exists(): void
    {
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('changelogs'));
    }

    public function test_trabajadores_table_exists(): void
    {
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('trabajadores'));
    }

    public function test_solicitudes_table_exists(): void
    {
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('solicitudes'));
    }

    public function test_has_admin_user(): void
    {
        $user = DB::table('users')->where('role', 'admin')->first();
        $this->assertNotNull($user, "Debe existir al menos un administrador");
    }

    public function test_welcome_page_returns_ok(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
