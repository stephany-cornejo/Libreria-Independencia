<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Services\ReporteService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportesTest extends TestCase
{
    use DatabaseTransactions;

    private string $sufijo;
    private Categoria $categoria;
    private Proveedor $proveedor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sufijo = substr(uniqid(), -6);
        $this->categoria = Categoria::create(['nombre' => 'Cat '.$this->sufijo]);
        $this->proveedor = Proveedor::create(['nombre' => 'Prov '.$this->sufijo]);
    }

    private function producto(string $clave, int $disponible, int $minimo): Producto
    {
        return Producto::create([
            'codigo' => $clave.$this->sufijo,
            'nombre' => 'Producto '.$clave.$this->sufijo,
            'id_categoria' => $this->categoria->getKey(),
            'id_proveedor' => $this->proveedor->getKey(),
            'precio_compra' => 1, 'precio_venta' => 2,
            'cantidad_disponible' => $disponible, 'existencia_minima' => $minimo,
            'fecha_ingreso' => now()->toDateString(), 'estado' => 'activo',
        ]);
    }

    private function venta(Producto $p, User $u, int $cantidad): void
    {
        Movimiento::create([
            'id_producto' => $p->getKey(), 'id_usuario' => $u->getKey(),
            'tipo' => 'salida', 'motivo' => 'venta', 'cantidad' => $cantidad,
            'existencia_resultante' => max(0, $p->cantidad_disponible - $cantidad),
            'fecha' => now(),
        ]);
    }

    public function test_stock_bajo_incluye_productos_en_el_minimo_y_por_debajo(): void
    {
        $this->producto('A', 2, 5);
        $this->producto('B', 5, 5);
        $this->producto('C', 10, 3);

        $codigos = app(ReporteService::class)->stockBajo()->pluck('codigo');

        $this->assertTrue($codigos->contains('A'.$this->sufijo));
        $this->assertTrue($codigos->contains('B'.$this->sufijo));
        $this->assertFalse($codigos->contains('C'.$this->sufijo));
    }

    public function test_agotados_solo_incluye_existencia_cero(): void
    {
        $this->producto('A', 0, 3);
        $this->producto('B', 1, 3);

        $codigos = app(ReporteService::class)->agotados()->pluck('codigo');

        $this->assertTrue($codigos->contains('A'.$this->sufijo));
        $this->assertFalse($codigos->contains('B'.$this->sufijo));
    }

    public function test_mas_vendidos_ordena_por_unidades_vendidas(): void
    {
        $usuario = User::factory()->create();
        $a = $this->producto('A', 20, 2);
        $b = $this->producto('B', 20, 2);

        $this->venta($a, $usuario, 3);
        $this->venta($a, $usuario, 2);
        $this->venta($b, $usuario, 1);

        $ventas = app(ReporteService::class)
            ->masVendidos(now()->subDay(), now()->addDay(), 1000)
            ->whereIn('codigo', ['A'.$this->sufijo, 'B'.$this->sufijo])
            ->values();

        $this->assertSame('A'.$this->sufijo, $ventas[0]->codigo);
        $this->assertEquals(5, $ventas[0]->total_vendido);
        $this->assertEquals(1, $ventas[1]->total_vendido);
    }

    public function test_poca_rotacion_incluye_productos_sin_movimientos(): void
    {
        $usuario = User::factory()->create();
        $quieto = $this->producto('A', 10, 2);
        $activo = $this->producto('B', 10, 2);

        $this->venta($activo, $usuario, 1);
        $this->venta($activo, $usuario, 1);

        $codigos = app(ReporteService::class)
            ->pocaRotacion(now()->subDay(), now()->addDay(), 0)
            ->pluck('codigo');

        $this->assertTrue($codigos->contains($quieto->codigo));
        $this->assertFalse($codigos->contains($activo->codigo));
    }

    public function test_historial_filtra_por_tipo_y_usuario(): void
    {
        $usuarioA = User::factory()->create();
        $usuarioB = User::factory()->create();
        $p = $this->producto('A', 20, 2);

        $this->venta($p, $usuarioA, 1);
        $this->venta($p, $usuarioB, 1);

        $historial = app(ReporteService::class)->historial([
            'tipo' => 'salida', 'id_usuario' => $usuarioA->getKey(), 'id_producto' => $p->getKey(),
        ]);

        $this->assertCount(1, $historial->items());
    }
}
