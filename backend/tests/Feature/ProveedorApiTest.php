<?php

namespace Tests\Feature;

use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProveedorApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(
            'mysql',
            DB::connection()->getDriverName()
        );

        $this->assertSame(
            'libreria_independencia_testing',
            DB::connection()->getDatabaseName()
        );
    }

    private function usuarioConRol(string $nombre): User
    {
        $rol = Rol::firstOrCreate(['nombre' => $nombre]);

        return User::factory()->create([
            'id_rol' => $rol->id_rol,
        ]);
    }

    public function test_solo_administrador_puede_consultar_proveedores(): void
    {
        $this->getJson('/api/proveedores')
            ->assertUnauthorized();

        $this->actingAs($this->usuarioConRol('Empleado'))
            ->getJson('/api/proveedores')
            ->assertForbidden();

        $this->actingAs($this->usuarioConRol('Administrador'))
            ->getJson('/api/proveedores')
            ->assertOk()
            ->assertJsonStructure([
                'data', 'current_page', 'per_page'
            ]);
    }

    public function test_busca_por_datos_de_contacto(): void
    {
        $sufijo = Str::random(9);

        $proveedor = Proveedor::create([
            'nombre' => "Proveedor {$sufijo}",
            'telefono' => '7777-1234',
            'email' => "ventas.{$sufijo}@example.com",
            'direccion' => "Calle {$sufijo}",
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuarioConRol('Administrador'));

        $busquedas = [
            "Proveedor {$sufijo}",
            "ventas.{$sufijo}@example.com",
            '7777-1234',
            "Calle {$sufijo}",
        ];

        foreach ($busquedas as $texto) {
            $this->getJson('/api/proveedores?'.http_build_query([
                'q' => $texto,
            ]))
                ->assertOk()
                ->assertJsonFragment([
                    'id_proveedor' => $proveedor->id_proveedor
                ]);
        }
    }

    public function test_combina_busqueda_y_estado(): void
    {
        $sufijo = Str::random(9);

        $activo = Proveedor::create([
            'nombre' => "Distribuidora {$sufijo} Activa",
            'estado' => 'activo',
        ]);

        $inactivo = Proveedor::create([
            'nombre' => "Distribuidora {$sufijo} Inactiva",
            'estado' => 'inactivo',
        ]);

        $this->actingAs($this->usuarioConRol('Administrador'))
            ->getJson('/api/proveedores?'.http_build_query([
                'q' => $sufijo,
                'estado' => 'inactivo',
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id_proveedor' => $inactivo->id_proveedor
            ])
            ->assertJsonMissing([
                'id_proveedor' => $activo->id_proveedor
            ]);
    }

    public function test_consulta_detalle_y_devuelve_404(): void
    {
        $proveedor = Proveedor::create([
            'nombre' => 'Detalle proveedor '.Str::random(9),
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuarioConRol('Administrador'))
            ->getJson("/api/proveedores/{$proveedor->id_proveedor}")
            ->assertOk()
            ->assertJsonPath(
                'id_proveedor',
                $proveedor->id_proveedor
            )
            ->assertJsonPath('productos_count', 0);

        $this->getJson('/api/proveedores/999999999')
            ->assertNotFound();
    }

    public function test_rechaza_estado_invalido(): void
    {
        $this->actingAs($this->usuarioConRol('Administrador'))
            ->getJson('/api/proveedores?estado=pendiente')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }
}
