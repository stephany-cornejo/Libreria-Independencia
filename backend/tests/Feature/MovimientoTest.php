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

class MovimientoTest extends TestCase
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

    private function crearLibro(array $atributos = []): Producto
    {
        $categoria = Categoria::firstOrCreate([
            'nombre' => 'Narrativa',
        ], [
            'estado' => 'activo',
        ]);

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

    private function datosValidos(Producto $producto, array $sobrescritura = []): array
    {
        return array_merge([
            'tipo' => 'entrada',
            'motivo' => 'compra',
            'id_producto' => $producto->id_producto,
            'cantidad' => 5,
            'observaciones' => 'Compra a proveedor',
        ], $sobrescritura);
    }

    public function test_visitante_es_redirigido_al_login_desde_movimientos(): void
    {
        $this->get('/panel/movimientos')
            ->assertRedirect(route('login'));

        $this->get('/panel/movimientos/crear')
            ->assertRedirect(route('login'));
    }

    public function test_entrada_aumenta_existencias_y_registra_movimiento(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro(['cantidad_disponible' => 8]);

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro))
            ->assertRedirect(route('movimientos.index'))
            ->assertSessionHas('exito');

        $this->assertSame(13, $libro->fresh()->cantidad_disponible);

        $this->assertDatabaseHas('movimientos', [
            'tipo' => 'entrada',
            'motivo' => 'compra',
            'id_producto' => $libro->id_producto,
            'id_usuario' => $usuario->id_usuario,
            'cantidad' => 5,
            'existencia_resultante' => 13,
        ]);
    }

    public function test_salida_disminuye_existencias_y_registra_movimiento(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro(['cantidad_disponible' => 8]);

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'tipo' => 'salida',
                'motivo' => 'venta',
                'cantidad' => 3,
                'observaciones' => 'Venta en mostrador',
            ]))
            ->assertRedirect(route('movimientos.index'));

        $this->assertSame(5, $libro->fresh()->cantidad_disponible);

        $this->assertDatabaseHas('movimientos', [
            'tipo' => 'salida',
            'motivo' => 'venta',
            'existencia_resultante' => 5,
        ]);
    }

    public function test_salida_con_stock_insuficiente_es_rechazada_y_no_modifica_nada(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro(['cantidad_disponible' => 2]);

        $this->actingAs($usuario)
            ->from('/panel/movimientos/crear')
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'tipo' => 'salida',
                'motivo' => 'venta',
                'cantidad' => 5,
            ]))
            ->assertRedirect(route('movimientos.crear'))
            ->assertSessionHas('error');

        $this->assertSame(2, $libro->fresh()->cantidad_disponible);
        $this->assertDatabaseCount('movimientos', 0);

        $this->assertStringContainsString(
            'Existencias insuficientes',
            session('error')
        );
    }

    public function test_salida_exactamente_por_la_existencia_disponible_es_valida(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro(['cantidad_disponible' => 4]);

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'tipo' => 'salida',
                'motivo' => 'perdida',
                'cantidad' => 4,
            ]))
            ->assertRedirect(route('movimientos.index'));

        $this->assertSame(0, $libro->fresh()->cantidad_disponible);
        $this->assertDatabaseHas('movimientos', [
            'tipo' => 'salida',
            'motivo' => 'perdida',
            'existencia_resultante' => 0,
        ]);
    }

    public function test_motivo_incompatible_con_el_tipo_es_rechazado(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro();

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'tipo' => 'salida',
                'motivo' => 'compra',
            ]))
            ->assertSessionHasErrors('motivo');

        $this->assertDatabaseCount('movimientos', 0);
    }

    public function test_tipo_invalido_es_rechazado(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro();

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'tipo' => 'traspaso',
            ]))
            ->assertSessionHasErrors('tipo');
    }

    public function test_cantidad_cero_o_negativa_es_rechazada(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro();

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'cantidad' => 0,
            ]))
            ->assertSessionHasErrors('cantidad');

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'cantidad' => -3,
            ]))
            ->assertSessionHasErrors('cantidad');

        $this->assertDatabaseCount('movimientos', 0);
    }

    public function test_producto_inactivo_no_acepta_movimientos(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro(['estado' => 'descontinuado']);

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro))
            ->assertSessionHasErrors('id_producto');
    }

    public function test_ajuste_es_valido_para_entrada_y_salida(): void
    {
        $usuario = $this->crearUsuario();

        $entrada = $this->crearLibro(['cantidad_disponible' => 10]);
        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($entrada, [
                'tipo' => 'entrada',
                'motivo' => 'ajuste',
                'cantidad' => 2,
            ]))
            ->assertRedirect(route('movimientos.index'));

        $this->assertSame(12, $entrada->fresh()->cantidad_disponible);

        $salida = $this->crearLibro(['cantidad_disponible' => 10]);
        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($salida, [
                'tipo' => 'salida',
                'motivo' => 'ajuste',
                'cantidad' => 1,
            ]))
            ->assertRedirect(route('movimientos.index'));

        $this->assertSame(9, $salida->fresh()->cantidad_disponible);
    }

    public function test_historial_muestra_movimientos_y_filtra_por_tipo(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro();

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro));

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'tipo' => 'salida',
                'motivo' => 'venta',
                'cantidad' => 2,
                'observaciones' => 'Atención en mostrador',
            ]));

        $this->actingAs($usuario)
            ->get('/panel/movimientos')
            ->assertOk()
            ->assertSee('Entrada')
            ->assertSee('Salida')
            ->assertSee('Compra')
            ->assertSee($libro->nombre);

        $this->actingAs($usuario)
            ->get('/panel/movimientos?tipo=salida')
            ->assertOk()
            ->assertDontSee('Compra')
            ->assertSee('Venta');
    }

    public function test_ajustes_consecutivos_mantienen_la_existencia_resultante(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro(['cantidad_disponible' => 10]);

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'tipo' => 'salida',
                'motivo' => 'dano',
                'cantidad' => 3,
            ]));

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'tipo' => 'entrada',
                'motivo' => 'reposicion',
                'cantidad' => 5,
            ]));

        $this->assertSame(12, $libro->fresh()->cantidad_disponible);

        $this->assertDatabaseHas('movimientos', [
            'id_producto' => $libro->id_producto,
            'tipo' => 'entrada',
            'motivo' => 'reposicion',
            'existencia_resultante' => 12,
        ]);
    }

    public function test_observaciones_opcionales_se_guardan(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro();

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro, [
                'observaciones' => null,
            ]))
            ->assertRedirect(route('movimientos.index'));

        $this->assertDatabaseHas('movimientos', [
            'id_producto' => $libro->id_producto,
            'observaciones' => null,
        ]);
    }

    public function test_el_historial_no_permite_modificar_movimientos(): void
    {
        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro();

        $this->actingAs($usuario)
            ->post('/panel/movimientos', $this->datosValidos($libro));

        $movimiento = Movimiento::firstOrFail();

        // No existen rutas de edición/eliminación: el historial es inmutable.
        $this->get("/panel/movimientos/{$movimiento->id_movimiento}/editar")
            ->assertNotFound();
    }
}
