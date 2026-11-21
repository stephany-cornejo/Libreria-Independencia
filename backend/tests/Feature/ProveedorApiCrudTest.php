<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProveedorApiCrudTest extends TestCase
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

    private function usuario(string $rol): User
    {
        $registro = Rol::firstOrCreate(['nombre' => $rol]);

        return User::factory()->create([
            'id_rol' => $registro->id_rol,
        ]);
    }

    public function test_administrador_registra_proveedor(): void
    {
        $nombre = 'Proveedor API '.Str::random(8);

        $this->actingAs($this->usuario('Administrador'))
            ->postJson('/api/proveedores', [
                'nombre' => $nombre,
                'telefono' => '+503 2222-3333',
                'email' => 'ventas@example.com',
                'direccion' => 'San Salvador',
                'estado' => 'activo',
            ])
            ->assertCreated()
            ->assertJsonPath('proveedor.nombre', $nombre)
            ->assertJsonPath('proveedor.productos_count', 0);

        $this->assertDatabaseHas('proveedores', [
            'nombre' => $nombre,
            'telefono' => '+503 2222-3333',
        ]);
    }

    public function test_valida_campos_y_nombres_duplicados(): void
    {
        $nombre = 'Duplicado API '.Str::random(8);

        Proveedor::create([
            'nombre' => $nombre,
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuario('Administrador'));

        $this->postJson('/api/proveedores', [
            'nombre' => '',
            'telefono' => 'abc123',
            'email' => 'correo-invalido',
            'estado' => 'pendiente',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nombre',
                'telefono',
                'email',
                'estado',
            ]);

        $this->postJson('/api/proveedores', [
            'nombre' => $nombre,
            'estado' => 'activo',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');
    }

    public function test_administrador_edita_proveedor(): void
    {
        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor original '.Str::random(8),
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuario('Administrador'))
            ->putJson("/api/proveedores/{$proveedor->id_proveedor}", [
                'nombre' => $proveedor->nombre,
                'telefono' => '2222-4444',
                'email' => 'actualizado@example.com',
                'direccion' => 'Santa Tecla',
                'estado' => 'activo',
            ])
            ->assertOk()
            ->assertJsonPath(
                'proveedor.email',
                'actualizado@example.com'
            );

        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
            'direccion' => 'Santa Tecla',
        ]);
    }

    public function test_activa_y_desactiva_proveedor(): void
    {
        $proveedor = Proveedor::create([
            'nombre' => 'Estado API '.Str::random(8),
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuario('Administrador'));

        $url = "/api/proveedores/{$proveedor->id_proveedor}/estado";

        $this->patchJson($url, [
            'estado' => 'inactivo',
        ])
            ->assertOk()
            ->assertJsonPath('proveedor.estado', 'inactivo');

        $this->patchJson($url, [
            'estado' => 'activo',
        ])
            ->assertOk()
            ->assertJsonPath('proveedor.estado', 'activo');

        $this->patchJson($url, [
            'estado' => 'suspendido',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }

    public function test_elimina_proveedor_sin_productos(): void
    {
        $proveedor = Proveedor::create([
            'nombre' => 'Temporal API '.Str::random(8),
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuario('Administrador'))
            ->deleteJson("/api/proveedores/{$proveedor->id_proveedor}")
            ->assertOk()
            ->assertJsonPath(
                'mensaje',
                'Proveedor eliminado correctamente.'
            );

        $this->assertDatabaseMissing('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
        ]);
    }

    public function test_no_elimina_proveedor_con_productos(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Categoria API '.Str::random(8),
            'estado' => 'activo',
        ]);

        $proveedor = Proveedor::create([
            'nombre' => 'Relacionado API '.Str::random(8),
            'estado' => 'activo',
        ]);

        Producto::create([
            'codigo' => 'API-'.Str::upper(Str::random(8)),
            'nombre' => 'Libro de prueba',
            'id_categoria' => $categoria->id_categoria,
            'id_proveedor' => $proveedor->id_proveedor,
            'precio_compra' => 5,
            'precio_venta' => 10,
            'cantidad_disponible' => 2,
            'existencia_minima' => 1,
            'fecha_ingreso' => today()->toDateString(),
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuario('Administrador'))
            ->deleteJson("/api/proveedores/{$proveedor->id_proveedor}")
            ->assertStatus(409)
            ->assertJsonPath(
                'mensaje',
                'No se puede eliminar: el proveedor tiene libros asociados.'
            );

        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
        ]);
    }

    public function test_verifica_permisos_y_registros_inexistentes(): void
    {
        $this->postJson('/api/proveedores', [
            'nombre' => 'Sin sesion',
            'estado' => 'activo',
        ])->assertUnauthorized();

        $this->actingAs($this->usuario('Empleado'))
            ->postJson('/api/proveedores', [
                'nombre' => 'Sin permiso',
                'estado' => 'activo',
            ])
            ->assertForbidden();

        $this->actingAs($this->usuario('Administrador'))
            ->deleteJson('/api/proveedores/999999999')
            ->assertNotFound();
    }
}
