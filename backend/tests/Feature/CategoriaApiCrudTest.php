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

class CategoriaApiCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('mysql', DB::connection()->getDriverName());

        $this->assertSame(
            'libreria_independencia_testing',
            DB::connection()->getDatabaseName()
        );
    }

    private function usuario(string $rol): User
    {
        $registroRol = Rol::firstOrCreate(['nombre' => $rol]);

        return User::factory()->create([
            'id_rol' => $registroRol->id_rol,
        ]);
    }

    public function test_administrador_crea_categoria(): void
    {
        $nombre = 'Categoria API '.Str::random(8);

        $this->actingAs($this->usuario('Administrador'))
            ->postJson('/api/categorias', [
                'nombre' => $nombre,
                'descripcion' => 'Creada mediante la API',
                'estado' => 'activo',
            ])
            ->assertCreated()
            ->assertJsonPath('categoria.nombre', $nombre)
            ->assertJsonPath('categoria.productos_count', 0);

        $this->assertDatabaseHas('categorias', [
            'nombre' => $nombre,
            'estado' => 'activo',
        ]);
    }

    public function test_rechaza_datos_invalidos_y_duplicados(): void
    {
        $nombre = 'Duplicada API '.Str::random(8);

        Categoria::create([
            'nombre' => $nombre,
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuario('Administrador'))
            ->postJson('/api/categorias', [
                'nombre' => '',
                'estado' => 'otro',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'estado']);

        $this->postJson('/api/categorias', [
            'nombre' => $nombre,
            'estado' => 'activo',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');
    }

    public function test_actualiza_categoria_y_su_estado(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Categoria original '.Str::random(8),
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuario('Administrador'))
            ->putJson("/api/categorias/{$categoria->id_categoria}", [
                'nombre' => $categoria->nombre,
                'descripcion' => 'Descripcion actualizada',
                'estado' => 'activo',
            ])
            ->assertOk()
            ->assertJsonPath(
                'categoria.descripcion',
                'Descripcion actualizada'
            );

        $this->patchJson(
            "/api/categorias/{$categoria->id_categoria}/estado",
            ['estado' => 'inactivo']
        )
            ->assertOk()
            ->assertJsonPath('categoria.estado', 'inactivo');

        $this->patchJson(
            "/api/categorias/{$categoria->id_categoria}/estado",
            ['estado' => 'otro']
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');

        $this->assertDatabaseHas('categorias', [
            'id_categoria' => $categoria->id_categoria,
            'estado' => 'inactivo',
        ]);
    }

    public function test_elimina_categoria_sin_productos(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Temporal API '.Str::random(8),
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuario('Administrador'))
            ->deleteJson("/api/categorias/{$categoria->id_categoria}")
            ->assertOk()
            ->assertJsonPath(
                'mensaje',
                'Categoría eliminada correctamente.'
            );

        $this->assertDatabaseMissing('categorias', [
            'id_categoria' => $categoria->id_categoria,
        ]);
    }

    public function test_impide_eliminar_categoria_con_productos(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Relacionada API '.Str::random(8),
            'estado' => 'activo',
        ]);

        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor API '.Str::random(8),
            'estado' => 'activo',
        ]);

        Producto::create([
            'codigo' => 'API-'.Str::upper(Str::random(8)),
            'nombre' => 'Libro de prueba',
            'id_categoria' => $categoria->id_categoria,
            'id_proveedor' => $proveedor->id_proveedor,
            'precio_compra' => 0,
            'precio_venta' => 10,
            'cantidad_disponible' => 1,
            'existencia_minima' => 1,
            'fecha_ingreso' => today()->toDateString(),
            'estado' => 'activo',
        ]);

        $this->actingAs($this->usuario('Administrador'))
            ->deleteJson("/api/categorias/{$categoria->id_categoria}")
            ->assertStatus(409)
            ->assertJsonPath(
                'mensaje',
                'No se puede eliminar: la categoría tiene libros asociados.'
            );

        $this->assertDatabaseHas('categorias', [
            'id_categoria' => $categoria->id_categoria,
        ]);
    }

    public function test_empleado_no_puede_crear_categorias(): void
    {
        $this->actingAs($this->usuario('Empleado'))
            ->postJson('/api/categorias', [
                'nombre' => 'Sin permiso '.Str::random(8),
                'estado' => 'activo',
            ])
            ->assertForbidden();
    }
}
