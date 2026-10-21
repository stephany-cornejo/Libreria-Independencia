<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class PersistenciaInventarioTest extends TestCase
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

    private function crearProducto(array $atributos = []): Producto
    {
        $categoria = Categoria::create([
            'nombre' => 'Categoría de prueba',
            'estado' => 'activo',
        ]);

        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor de prueba',
            'estado' => 'activo',
        ]);

        return Producto::create(array_merge([
            'codigo' => Str::upper(Str::random(12)),
            'nombre' => 'Producto de prueba',
            'id_categoria' => $categoria->id_categoria,
            'id_proveedor' => $proveedor->id_proveedor,
            'precio_compra' => '2.50',
            'precio_venta' => '4.00',
            'cantidad_disponible' => 5,
            'existencia_minima' => 2,
            'fecha_ingreso' => today()->toDateString(),
            'estado' => 'activo',
        ], $atributos));
    }

    private function crearMovimiento(
        Producto $producto,
        array $atributos = []
    ): Movimiento {
        $usuario = User::factory()->create();

        return Movimiento::create(array_merge([
            'tipo' => 'entrada',
            'motivo' => 'compra',
            'id_producto' => $producto->id_producto,
            'id_usuario' => $usuario->id_usuario,
            'cantidad' => 5,
            'existencia_resultante' => 5,
            'fecha' => now(),
        ], $atributos));
    }

    /**
     * Comprueba que la base de datos rechaza la operación con un
     * código de violación de restricción CHECK. MySQL 8.x devuelve
     * 3819 y MariaDB 4025 para el mismo tipo de violación, por lo
     * que se aceptan ambos códigos y la prueba sigue siendo
     * significativa en cualquiera de los dos motores.
     */
    private function comprobarRechazoMysql(
        callable $operacion,
        int|array $codigosEsperados
    ): void {
        try {
            $operacion();
        } catch (QueryException $exception) {
            $codigo = (int) $exception->errorInfo[1];

            $this->assertContains(
                $codigo,
                (array) $codigosEsperados,
                'Se esperaba un rechazo con código '
                    .implode(' o ', (array) $codigosEsperados)
                    .", se recibió {$codigo}."
            );

            return;
        }

        $this->fail('La base de datos debía rechazar esta operación.');
    }

    public function test_guarda_producto_y_consulta_sus_relaciones(): void
    {
        $producto = $this->crearProducto();

        $guardado = Producto::with(['categoria', 'proveedor'])
            ->findOrFail($producto->id_producto);

        $this->assertSame('2.50', $guardado->precio_compra);
        $this->assertSame(5, $guardado->cantidad_disponible);

        $this->assertSame(
            'Categoría de prueba',
            $guardado->categoria->nombre
        );

        $this->assertSame(
            'Proveedor de prueba',
            $guardado->proveedor->nombre
        );

        $this->assertTrue(
            $guardado->categoria->productos->contains($guardado)
        );

        $this->assertTrue(
            $guardado->proveedor->productos->contains($guardado)
        );

        $this->assertDatabaseHas('productos', [
            'id_producto' => $producto->id_producto,
            'codigo' => $producto->codigo,
        ]);
    }

    public function test_usuario_protege_contrasena_y_consulta_rol(): void
    {
        $usuario = User::factory()->create([
            'contrasena_hash' => 'ClaveDePrueba123!',
        ])->fresh();

        $this->assertTrue(
            Hash::check(
                'ClaveDePrueba123!',
                $usuario->contrasena_hash
            )
        );

        $this->assertArrayNotHasKey(
            'contrasena_hash',
            $usuario->toArray()
        );

        $this->assertSame(
            'contrasena_hash',
            $usuario->getAuthPasswordName()
        );

        $this->assertSame('Empleado', $usuario->rol->nombre);

        $this->assertSame(
            $usuario->id_usuario,
            $usuario->getAuthIdentifier()
        );
    }

    public function test_rechaza_codigo_de_producto_duplicado(): void
    {
        $producto = $this->crearProducto();

        $this->comprobarRechazoMysql(
            fn () => $this->crearProducto([
                'codigo' => $producto->codigo,
            ]),
            1062
        );
    }

    public function test_rechaza_precio_compra_negativo(): void
    {
        $producto = $this->crearProducto();

        // 3819 (MySQL) o 4025 (MariaDB): violación de CHECK.
        $this->comprobarRechazoMysql(
            fn () => $producto->update([
                'precio_compra' => '-1.00',
            ]),
            [3819, 4025]
        );
    }

    public function test_rechaza_precio_venta_negativo(): void
    {
        $producto = $this->crearProducto();

        // 3819 (MySQL) o 4025 (MariaDB): violación de CHECK.
        $this->comprobarRechazoMysql(
            fn () => $producto->update([
                'precio_venta' => '-1.00',
            ]),
            [3819, 4025]
        );
    }

    public function test_rechaza_existencia_negativa(): void
    {
        $producto = $this->crearProducto();

        $this->comprobarRechazoMysql(
            fn () => $producto->update([
                'cantidad_disponible' => -1,
            ]),
            1264
        );
    }

    public function test_rechaza_movimiento_con_cantidad_cero(): void
    {
        $producto = $this->crearProducto();

        // 3819 (MySQL) o 4025 (MariaDB): violación de CHECK.
        $this->comprobarRechazoMysql(
            fn () => $this->crearMovimiento($producto, [
                'cantidad' => 0,
            ]),
            [3819, 4025]
        );
    }

    public function test_rechaza_categoria_inexistente(): void
    {
        $this->comprobarRechazoMysql(
            fn () => $this->crearProducto([
                'id_categoria' => 4294967295,
            ]),
            1452
        );
    }

    public function test_conserva_producto_con_movimientos(): void
    {
        $producto = $this->crearProducto();
        $movimiento = $this->crearMovimiento($producto);

        $this->assertSame(
            $producto->id_producto,
            $movimiento->producto->id_producto
        );

        $this->assertSame(
            'Empleado',
            $movimiento->usuario->rol->nombre
        );

        $this->comprobarRechazoMysql(
            fn () => $producto->delete(),
            1451
        );

        $this->assertDatabaseHas('productos', [
            'id_producto' => $producto->id_producto,
        ]);
    }

    public function test_transaccion_revierte_stock_y_movimiento(): void
    {
        $producto = $this->crearProducto();

        $movimientosAntes = Movimiento::count();

        try {
            DB::transaction(function () use ($producto): void {
                $producto->update([
                    'cantidad_disponible' => 4,
                ]);

                $this->crearMovimiento($producto, [
                    'tipo' => 'salida',
                    'motivo' => 'venta',
                    'cantidad' => 1,
                    'existencia_resultante' => 4,
                ]);

                throw new RuntimeException('Fallo simulado');
            });

            $this->fail('La transacción debía lanzar la excepción.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Fallo simulado',
                $exception->getMessage()
            );
        }

        $this->assertSame(
            5,
            $producto->fresh()->cantidad_disponible
        );

        $this->assertSame(
            $movimientosAntes,
            Movimiento::count()
        );
    }
}
