<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class InventarioTest extends TestCase
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

    private function crearUsuario(string $rol = 'Empleado'): User
    {
        $modeloRol = Rol::firstOrCreate([
            'nombre' => $rol,
        ]);

        return User::factory()->create([
            'id_rol' => $modeloRol->id_rol,
            'contrasena_hash' => 'ClaveDePrueba123!',
        ]);
    }

    private function crearCategoria(string $nombre = 'Narrativa'): Categoria
    {
        return Categoria::firstOrCreate([
            'nombre' => $nombre,
        ], [
            'estado' => 'activo',
        ]);
    }

    private function crearLibro(array $atributos = []): Producto
    {
        $categoria = $this->crearCategoria();

        return Producto::create(array_merge([
            'codigo' => 'LIB-'.Str::upper(Str::random(6)),
            'nombre' => 'Libro de prueba',
            'autor' => 'Autor de prueba',
            'id_categoria' => $categoria->id_categoria,
            'precio_compra' => 0,
            'precio_venta' => '10.00',
            'cantidad_disponible' => 8,
            'existencia_minima' => 3,
            'fecha_ingreso' => today()->toDateString(),
            'estado' => 'activo',
        ], $atributos));
    }

    private function datosValidos(Categoria $categoria): array
    {
        return [
            'nombre' => 'Cien años de soledad',
            'autor' => 'Gabriel García Márquez',
            'editorial' => 'Diana',
            'isbn' => '978-84-376-0494-7',
            'id_categoria' => $categoria->id_categoria,
            'precio' => '14.99',
            'cantidad' => 12,
        ];
    }

    public function test_visitante_es_redirigido_al_login_desde_el_inventario(): void
    {
        $this->get('/panel/inventario')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_empleado_consulta_el_inventario_y_ve_las_alertas_de_stock(): void
    {
        $this->crearLibro([
            'nombre' => 'Libro disponible',
            'cantidad_disponible' => 10,
            'existencia_minima' => 2,
        ]);
        $this->crearLibro([
            'nombre' => 'Libro con poco stock',
            'cantidad_disponible' => 2,
            'existencia_minima' => 3,
        ]);
        $this->crearLibro([
            'nombre' => 'Libro agotado',
            'cantidad_disponible' => 0,
            'existencia_minima' => 3,
        ]);

        $empleado = $this->crearUsuario();

        $this->actingAs($empleado)
            ->get('/panel/inventario')
            ->assertOk()
            ->assertSee('Inventario de libros')
            ->assertSee('Libro disponible')
            ->assertSee('Disponible')
            ->assertSee('Stock bajo')
            ->assertSee('Agotado');
    }

    public function test_indice_filtra_por_texto_de_titulo_o_autor(): void
    {
        $this->crearLibro([
            'nombre' => 'Cien años de soledad',
            'autor' => 'Gabriel García Márquez',
        ]);
        $this->crearLibro([
            'nombre' => 'El principito',
            'autor' => 'Antoine de Saint-Exupéry',
        ]);

        $empleado = $this->crearUsuario();

        $this->actingAs($empleado)
            ->get('/panel/inventario?q=García')
            ->assertOk()
            ->assertSee('Cien años de soledad')
            ->assertDontSee('El principito');

        $this->actingAs($empleado)
            ->get('/panel/inventario?q=principito')
            ->assertOk()
            ->assertSee('El principito')
            ->assertDontSee('Cien años de soledad');
    }

    public function test_indice_filtra_por_categoria(): void
    {
        $narrativa = $this->crearCategoria('Narrativa');
        $escolar = $this->crearCategoria('Útiles escolares');

        $this->crearLibro([
            'nombre' => 'Novela de la casa',
            'id_categoria' => $narrativa->id_categoria,
        ]);
        $this->crearLibro([
            'nombre' => 'Cuaderno rayado',
            'id_categoria' => $escolar->id_categoria,
        ]);

        $empleado = $this->crearUsuario();

        $this->actingAs($empleado)
            ->get('/panel/inventario?categoria='.$escolar->id_categoria)
            ->assertOk()
            ->assertSee('Cuaderno rayado')
            ->assertDontSee('Novela de la casa');
    }

    public function test_indice_filtra_por_disponibilidad(): void
    {
        $this->crearLibro([
            'nombre' => 'Título agotadísimo',
            'cantidad_disponible' => 0,
        ]);
        $this->crearLibro([
            'nombre' => 'Título con existencias',
            'cantidad_disponible' => 9,
        ]);

        $empleado = $this->crearUsuario();

        $this->actingAs($empleado)
            ->get('/panel/inventario?disponibilidad=agotado')
            ->assertOk()
            ->assertSee('Título agotadísimo')
            ->assertDontSee('Título con existencias');

        $this->actingAs($empleado)
            ->get('/panel/inventario?disponibilidad=disponible')
            ->assertOk()
            ->assertSee('Título con existencias')
            ->assertDontSee('Título agotadísimo');
    }

    public function test_registrar_libro_con_datos_validos_lo_guarda(): void
    {
        $categoria = $this->crearCategoria();
        $empleado = $this->crearUsuario();

        $respuesta = $this->actingAs($empleado)
            ->post(
                route('inventario.guardar'),
                $this->datosValidos($categoria)
            );

        $respuesta->assertRedirect(route('inventario.index'));
        $this->assertNotNull(session('exito'));

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Cien años de soledad',
            'autor' => 'Gabriel García Márquez',
            'editorial' => 'Diana',
            'isbn' => '978-84-376-0494-7',
            'id_categoria' => $categoria->id_categoria,
            'precio_venta' => '14.99',
            'cantidad_disponible' => 12,
        ]);
    }

    public function test_precio_no_numerico_muestra_error_en_espanol(): void
    {
        $categoria = $this->crearCategoria();
        $empleado = $this->crearUsuario();

        $datos = $this->datosValidos($categoria);
        $datos['precio'] = 'no es un número';

        $this->actingAs($empleado)
            ->from(route('inventario.crear'))
            ->post(route('inventario.guardar'), $datos)
            ->assertRedirect(route('inventario.crear'))
            ->assertSessionHasErrors('precio');

        $this->assertSame(
            'El precio debe ser un valor numérico.',
            session('errors')->first('precio')
        );

        $this->assertDatabaseMissing('productos', [
            'nombre' => 'Cien años de soledad',
        ]);
    }

    public function test_precio_negativo_muestra_error_en_espanol(): void
    {
        $categoria = $this->crearCategoria();
        $empleado = $this->crearUsuario();

        $datos = $this->datosValidos($categoria);
        $datos['precio'] = '-5';

        $this->actingAs($empleado)
            ->post(route('inventario.guardar'), $datos)
            ->assertSessionHasErrors('precio');

        $this->assertSame(
            'El precio debe ser un número positivo.',
            session('errors')->first('precio')
        );
    }

    public function test_cantidad_negativa_muestra_error_en_espanol(): void
    {
        $categoria = $this->crearCategoria();
        $empleado = $this->crearUsuario();

        $datos = $this->datosValidos($categoria);
        $datos['cantidad'] = -1;

        $this->actingAs($empleado)
            ->post(route('inventario.guardar'), $datos)
            ->assertSessionHasErrors('cantidad');

        $this->assertSame(
            'La cantidad no puede ser negativa.',
            session('errors')->first('cantidad')
        );
    }

    public function test_titulo_obligatorio_muestra_error_en_espanol(): void
    {
        $categoria = $this->crearCategoria();
        $empleado = $this->crearUsuario();

        $datos = $this->datosValidos($categoria);
        $datos['nombre'] = '';

        $this->actingAs($empleado)
            ->post(route('inventario.guardar'), $datos)
            ->assertSessionHasErrors('nombre');

        $this->assertSame(
            'El título es obligatorio.',
            session('errors')->first('nombre')
        );
    }

    public function test_categoria_obligatoria_muestra_error_en_espanol(): void
    {
        $categoria = $this->crearCategoria();
        $empleado = $this->crearUsuario();

        $datos = $this->datosValidos($categoria);
        $datos['id_categoria'] = '';

        $this->actingAs($empleado)
            ->post(route('inventario.guardar'), $datos)
            ->assertSessionHasErrors('id_categoria');

        $this->assertSame(
            'Debes seleccionar una categoría.',
            session('errors')->first('id_categoria')
        );
    }

    public function test_isbn_con_formato_invalido_muestra_error_en_espanol(): void
    {
        $categoria = $this->crearCategoria();
        $empleado = $this->crearUsuario();

        $datos = $this->datosValidos($categoria);
        $datos['isbn'] = '12345';

        $this->actingAs($empleado)
            ->post(route('inventario.guardar'), $datos)
            ->assertSessionHasErrors('isbn');

        $this->assertSame(
            'El ISBN no tiene un formato válido. Debe ser un ISBN-10 o ISBN-13 (se admiten guiones y espacios).',
            session('errors')->first('isbn')
        );
    }

    public function test_isbn_con_digito_verificador_incorrecto_muestra_error(): void
    {
        $categoria = $this->crearCategoria();
        $empleado = $this->crearUsuario();

        $datos = $this->datosValidos($categoria);
        // ISBN-13 con estructura válida pero dígito verificador erróneo.
        $datos['isbn'] = '978-84-376-0494-8';

        $this->actingAs($empleado)
            ->post(route('inventario.guardar'), $datos)
            ->assertSessionHasErrors('isbn');

        $this->assertSame(
            'El ISBN no es válido: el dígito verificador no coincide.',
            session('errors')->first('isbn')
        );
    }

    public function test_isbn_en_blanco_se_acepta(): void
    {
        $categoria = $this->crearCategoria();
        $empleado = $this->crearUsuario();

        $datos = $this->datosValidos($categoria);
        $datos['isbn'] = '';

        $this->actingAs($empleado)
            ->post(route('inventario.guardar'), $datos)
            ->assertRedirect(route('inventario.index'));

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Cien años de soledad',
            'isbn' => null,
        ]);
    }

    public function test_editar_libro_actualiza_sus_datos(): void
    {
        $producto = $this->crearLibro();
        $categoria = $this->crearCategoria('Poesía');
        $empleado = $this->crearUsuario();

        $this->actingAs($empleado)
            ->get(route('inventario.editar', $producto))
            ->assertOk()
            ->assertSee('Editar libro')
            ->assertSee('Libro de prueba');

        $this->actingAs($empleado)
            ->put(route('inventario.actualizar', $producto), [
                'nombre' => 'Título actualizado',
                'autor' => 'Autor nuevo',
                'editorial' => 'Editorial nueva',
                'isbn' => '0-306-40615-2',
                'id_categoria' => $categoria->id_categoria,
                'precio' => '25.50',
                'cantidad' => 4,
            ])
            ->assertRedirect(route('inventario.index'));

        $this->assertNotNull(session('exito'));

        $this->assertDatabaseHas('productos', [
            'id_producto' => $producto->id_producto,
            'nombre' => 'Título actualizado',
            'autor' => 'Autor nuevo',
            'editorial' => 'Editorial nueva',
            'isbn' => '0-306-40615-2',
            'precio_venta' => '25.50',
            'cantidad_disponible' => 4,
        ]);
    }

    public function test_eliminar_libro_sin_movimientos_lo_elimina(): void
    {
        $producto = $this->crearLibro();
        $empleado = $this->crearUsuario();

        $this->actingAs($empleado)
            ->delete(route('inventario.eliminar', $producto))
            ->assertRedirect(route('inventario.index'));

        $this->assertNotNull(session('exito'));

        $this->assertDatabaseMissing('productos', [
            'id_producto' => $producto->id_producto,
        ]);
    }

    public function test_no_se_puede_eliminar_libro_con_movimientos_registrados(): void
    {
        $producto = $this->crearLibro();
        $empleado = $this->crearUsuario();

        Movimiento::create([
            'tipo' => 'entrada',
            'motivo' => 'compra',
            'id_producto' => $producto->id_producto,
            'id_usuario' => $empleado->id_usuario,
            'cantidad' => 8,
            'existencia_resultante' => 8,
            'fecha' => now(),
        ]);

        $this->actingAs($empleado)
            ->delete(route('inventario.eliminar', $producto))
            ->assertRedirect();

        $this->assertSame(
            'No se puede eliminar: el libro tiene movimientos de inventario registrados.',
            session('error')
        );

        $this->assertDatabaseHas('productos', [
            'id_producto' => $producto->id_producto,
        ]);
    }
}
