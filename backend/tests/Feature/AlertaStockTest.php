<?php

namespace Tests\Feature;

use App\Events\StockBajo;
use App\Listeners\RegistrarAlertaStockBajo;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class AlertaStockTest extends TestCase
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

    private function crearUsuario(): User
    {
        $modeloRol = Rol::firstOrCreate([
            'nombre' => 'Empleado',
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
            'id_categoria' => $categoria->id_categoria,
            'precio_compra' => 0,
            'precio_venta' => '10.00',
            'cantidad_disponible' => 8,
            'existencia_minima' => 3,
            'fecha_ingreso' => today()->toDateString(),
            'estado' => 'activo',
        ], $atributos));
    }

    public function test_una_salida_hasta_el_minimo_dispara_el_evento_stock_bajo(): void
    {
        Event::fake([StockBajo::class]);

        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro(['cantidad_disponible' => 8]);

        $this->actingAs($usuario)
            ->post('/panel/movimientos', [
                'tipo' => 'salida',
                'motivo' => 'venta',
                'id_producto' => $libro->id_producto,
                'cantidad' => 5, // 8 - 5 = 3 = mínimo
                'observaciones' => 'Venta en mostrador',
            ])
            ->assertRedirect(route('movimientos.index'));

        $this->assertSame(3, $libro->fresh()->cantidad_disponible);

        Event::assertDispatched(StockBajo::class, function (StockBajo $evento) use ($libro): bool {
            return $evento->producto->id_producto === $libro->id_producto
                && $evento->producto->cantidad_disponible === 3;
        });
    }

    public function test_una_salida_por_encima_del_minimo_no_dispara_el_evento(): void
    {
        Event::fake([StockBajo::class]);

        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro(['cantidad_disponible' => 8]);

        $this->actingAs($usuario)
            ->post('/panel/movimientos', [
                'tipo' => 'salida',
                'motivo' => 'venta',
                'id_producto' => $libro->id_producto,
                'cantidad' => 2, // 8 - 2 = 6 > mínimo (3)
                'observaciones' => null,
            ])
            ->assertRedirect(route('movimientos.index'));

        Event::assertNotDispatched(StockBajo::class);
    }

    public function test_una_entrada_no_dispara_el_evento_stock_bajo(): void
    {
        Event::fake([StockBajo::class]);

        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro(['cantidad_disponible' => 1]);

        $this->actingAs($usuario)
            ->post('/panel/movimientos', [
                'tipo' => 'entrada',
                'motivo' => 'compra',
                'id_producto' => $libro->id_producto,
                'cantidad' => 10,
                'observaciones' => 'Reposición',
            ])
            ->assertRedirect(route('movimientos.index'));

        Event::assertNotDispatched(StockBajo::class);
    }

    public function test_un_movimiento_rechazado_no_dispara_el_evento(): void
    {
        Event::fake([StockBajo::class]);

        $usuario = $this->crearUsuario();
        $libro = $this->crearLibro(['cantidad_disponible' => 2]);

        $this->actingAs($usuario)
            ->post('/panel/movimientos', [
                'tipo' => 'salida',
                'motivo' => 'venta',
                'id_producto' => $libro->id_producto,
                'cantidad' => 99,
                'observaciones' => null,
            ])
            ->assertSessionHas('error');

        Event::assertNotDispatched(StockBajo::class);
    }

    public function test_el_listener_se_procesa_en_cola(): void
    {
        Queue::fake();

        event(new StockBajo($this->crearLibro([
            'cantidad_disponible' => 2,
        ])));

        // El listener ShouldQueue se encola envuelto en CallQueuedListener.
        Queue::assertPushed(
            CallQueuedListener::class,
            fn (CallQueuedListener $job): bool =>
                $job->class === RegistrarAlertaStockBajo::class
        );
    }
}
