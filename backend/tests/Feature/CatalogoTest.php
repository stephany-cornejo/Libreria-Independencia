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

class CatalogoTest extends TestCase
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

    private function crearUsuario(string $rol): User
    {
        $modeloRol = Rol::firstOrCreate([
            'nombre' => $rol,
        ]);

        return User::factory()->create([
            'id_rol' => $modeloRol->id_rol,
            'contrasena_hash' => 'ClaveDePrueba123!',
        ]);
    }

    private function crearLibro(
        Categoria $categoria,
        Proveedor $proveedor
    ): Producto {
        return Producto::create([
            'codigo' => 'LIB-'.Str::upper(Str::random(6)),
            'nombre' => 'Libro de prueba',
            'id_categoria' => $categoria->id_categoria,
            'id_proveedor' => $proveedor->id_proveedor,
            'precio_compra' => 0,
            'precio_venta' => '10.00',
            'cantidad_disponible' => 5,
            'existencia_minima' => 3,
            'fecha_ingreso' => today()->toDateString(),
            'estado' => 'activo',
        ]);
    }

    // ------------------------------------------------------------------
    // Autorización
    // ------------------------------------------------------------------

    public function test_visitante_es_redirigido_al_login_desde_los_catalogos(): void
    {
        $this->get('/panel/categorias')->assertRedirect(route('login'));
        $this->get('/panel/proveedores')->assertRedirect(route('login'));
    }

    public function test_empleado_es_denegado_en_los_catalogos(): void
    {
        $empleado = $this->crearUsuario('Empleado');

        $this->actingAs($empleado)->get('/panel/categorias')->assertStatus(403);
        $this->actingAs($empleado)->get('/panel/proveedores')->assertStatus(403);
        $this->actingAs($empleado)->post('/panel/categorias', [
            'nombre' => 'Intento',
            'estado' => 'activo',
        ])->assertStatus(403);
    }

    public function test_administrador_accede_a_los_catalogos(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->get('/panel/categorias')
            ->assertOk()
            ->assertSee('Categorías');

        $this->actingAs($administrador)
            ->get('/panel/proveedores')
            ->assertOk()
            ->assertSee('Proveedores');
    }

    // ------------------------------------------------------------------
    // Categorías
    // ------------------------------------------------------------------

    public function test_administrador_crea_categoria_valida(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->post('/panel/categorias', [
                'nombre' => 'Ciencia ficción',
                'descripcion' => 'Novelas de anticipación',
                'estado' => 'activo',
            ])
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('exito');

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Ciencia ficción',
            'descripcion' => 'Novelas de anticipación',
            'estado' => 'activo',
        ]);
    }

    public function test_categoria_requiere_nombre_y_estado_validos(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->post('/panel/categorias', ['nombre' => '', 'estado' => ''])
            ->assertSessionHasErrors(['nombre', 'estado']);

        $this->actingAs($administrador)
            ->post('/panel/categorias', [
                'nombre' => str_repeat('x', 51),
                'estado' => 'suspendido',
            ])
            ->assertSessionHasErrors(['nombre', 'estado']);
    }

    public function test_categoria_con_nombre_duplicado_es_rechazada(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        Categoria::create(['nombre' => 'Narrativa', 'estado' => 'activo']);

        $this->actingAs($administrador)
            ->post('/panel/categorias', [
                'nombre' => 'Narrativa',
                'estado' => 'activo',
            ])
            ->assertSessionHasErrors('nombre');

        $this->assertDatabaseCount('categorias', 1);
    }

    public function test_administrador_edita_categoria_y_puede_desactivarla(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $categoria = Categoria::create([
            'nombre' => 'Narrativa',
            'estado' => 'activo',
        ]);

        $this->actingAs($administrador)
            ->put("/panel/categorias/{$categoria->id_categoria}", [
                'nombre' => 'Narrativa hispanoamericana',
                'descripcion' => 'Clásicos y contemporáneos',
                'estado' => 'inactivo',
            ])
            ->assertRedirect(route('categorias.index'));

        $this->assertDatabaseHas('categorias', [
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'Narrativa hispanoamericana',
            'estado' => 'inactivo',
        ]);
    }

    public function test_categoria_sin_libros_se_puede_eliminar(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $categoria = Categoria::create(['nombre' => 'Temporal', 'estado' => 'activo']);

        $this->actingAs($administrador)
            ->delete("/panel/categorias/{$categoria->id_categoria}")
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('exito');

        $this->assertDatabaseMissing('categorias', [
            'id_categoria' => $categoria->id_categoria,
        ]);
    }

    public function test_categoria_con_libros_no_se_puede_eliminar(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $categoria = Categoria::create(['nombre' => 'Narrativa', 'estado' => 'activo']);
        $proveedor = Proveedor::create(['nombre' => 'Editorial X', 'estado' => 'activo']);
        $this->crearLibro($categoria, $proveedor);

        $this->actingAs($administrador)
            ->from('/panel/categorias')
            ->delete("/panel/categorias/{$categoria->id_categoria}")
            ->assertRedirect('/panel/categorias')
            ->assertSessionHas('error');

        $this->assertStringContainsString(
            'tiene libros asociados',
            session('error')
        );

        $this->assertDatabaseHas('categorias', [
            'id_categoria' => $categoria->id_categoria,
        ]);
    }

    // ------------------------------------------------------------------
    // Proveedores
    // ------------------------------------------------------------------

    public function test_administrador_crea_proveedor_valido(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->post('/panel/proveedores', [
                'nombre' => 'Distribuidora Norte',
                'telefono' => '02-5551234',
                'email' => 'ventas@norte.com',
                'direccion' => 'Av. Principal 123',
                'estado' => 'activo',
            ])
            ->assertRedirect(route('proveedores.index'))
            ->assertSessionHas('exito');

        $this->assertDatabaseHas('proveedores', [
            'nombre' => 'Distribuidora Norte',
            'email' => 'ventas@norte.com',
            'estado' => 'activo',
        ]);
    }

    public function test_proveedor_valida_campos_opcionales(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->post('/panel/proveedores', [
                'nombre' => '',
                'email' => 'no-es-correo',
                'estado' => 'activo',
            ])
            ->assertSessionHasErrors(['nombre', 'email']);

        $this->actingAs($administrador)
            ->post('/panel/proveedores', [
                'nombre' => str_repeat('x', 101),
                'estado' => 'suspendido',
            ])
            ->assertSessionHasErrors(['nombre', 'estado']);
    }

    public function test_proveedor_sin_libros_se_puede_eliminar(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $proveedor = Proveedor::create(['nombre' => 'Temporal SA', 'estado' => 'activo']);

        $this->actingAs($administrador)
            ->delete("/panel/proveedores/{$proveedor->id_proveedor}")
            ->assertRedirect(route('proveedores.index'));

        $this->assertDatabaseMissing('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
        ]);
    }

    public function test_proveedor_con_libros_no_se_puede_eliminar(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $categoria = Categoria::create(['nombre' => 'Narrativa', 'estado' => 'activo']);
        $proveedor = Proveedor::create(['nombre' => 'Editorial Y', 'estado' => 'activo']);
        $this->crearLibro($categoria, $proveedor);

        $this->actingAs($administrador)
            ->from('/panel/proveedores')
            ->delete("/panel/proveedores/{$proveedor->id_proveedor}")
            ->assertRedirect('/panel/proveedores')
            ->assertSessionHas('error');

        $this->assertStringContainsString(
            'tiene libros asociados',
            session('error')
        );

        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
        ]);
    }

    public function test_proveedor_duplicado_es_rechazado(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        Proveedor::create(['nombre' => 'Editorial Única', 'estado' => 'activo']);

        $this->actingAs($administrador)
            ->post('/panel/proveedores', [
                'nombre' => 'Editorial Única',
                'estado' => 'activo',
            ])
            ->assertSessionHasErrors('nombre');
    }

    // ------------------------------------------------------------------
    // Integración con el inventario
    // ------------------------------------------------------------------

    public function test_categoria_inactiva_no_aparece_en_el_formulario_de_libros(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        Categoria::create(['nombre' => 'Visible', 'estado' => 'activo']);
        Categoria::create(['nombre' => 'Oculta', 'estado' => 'inactivo']);

        $this->actingAs($administrador)
            ->get('/panel/inventario/crear')
            ->assertOk()
            ->assertSee('Visible')
            ->assertDontSee('Oculta');
    }
}
