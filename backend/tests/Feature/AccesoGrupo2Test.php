<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AccesoGrupo2Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (! app('router')->has('categorias.index')) {
            $this->markTestSkipped('Falta integrar el middleware "rol" y routes/grupo2.php.');
        }
    }

    private function usuario(string $rol): User
    {
        $r = Rol::firstOrCreate(['nombre' => $rol]);

        return User::factory()->create(['id_rol' => $r->getKey()]);
    }

    public function test_empleado_no_puede_entrar_a_categorias_ni_proveedores(): void
    {
        $empleado = $this->usuario('Empleado');

        $this->actingAs($empleado)->get(route('categorias.index'))->assertForbidden();
        $this->actingAs($empleado)->get(route('proveedores.index'))->assertForbidden();
    }

    public function test_empleado_puede_ver_existencias_pero_no_reportes_de_administrador(): void
    {
        $empleado = $this->usuario('Empleado');

        $this->actingAs($empleado)->get(route('reportes.existencias'))->assertOk();
        $this->actingAs($empleado)->get(route('reportes.mas-vendidos'))->assertForbidden();
    }

    public function test_administrador_accede_a_los_modulos(): void
    {
        $admin = $this->usuario('Administrador');

        $this->actingAs($admin)->get(route('categorias.index'))->assertOk();
        $this->actingAs($admin)->get(route('proveedores.index'))->assertOk();
        $this->actingAs($admin)->get(route('reportes.index'))->assertOk();
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('historial.index'))->assertRedirect();
    }
}
