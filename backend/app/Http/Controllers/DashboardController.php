<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        // ---- Indicadores generales (datos reales de MySQL) ----
        $indicadores = [
            'productos' => Producto::count(),
            'categorias' => Categoria::count(),
            'proveedores' => Proveedor::count(),
            'movimientos' => Movimiento::count(),
            'agotados' => Producto::where('stock', 0)->count(),
            'stock_bajo' => Producto::where('stock', '>', 0)->whereColumn('stock', '<=', 'stock_minimo')->count(),
            'entradas_mes' => (int) Movimiento::where('tipo', 'entrada')
                ->where('fecha_movimiento', '>=', now()->startOfMonth())->sum('cantidad'),
            'salidas_mes' => (int) Movimiento::where('tipo', 'salida')
                ->where('fecha_movimiento', '>=', now()->startOfMonth())->sum('cantidad'),
        ];

        // ---- Movimientos por día (últimos 30 días) ----
        $desde = now()->subDays(29)->startOfDay();

        $filas = Movimiento::query()
            ->where('fecha_movimiento', '>=', $desde)
            ->select(DB::raw('DATE(fecha_movimiento) as dia'), 'tipo', DB::raw('SUM(cantidad) as total'))
            ->groupBy('dia', 'tipo')
            ->get();

        $porDia = [];
        for ($d = $desde->copy(); $d->lte(now()); $d->addDay()) {
            $porDia[$d->toDateString()] = ['dia' => $d->format('d/m'), 'entradas' => 0, 'salidas' => 0];
        }
        foreach ($filas as $f) {
            $clave = Carbon::parse($f->dia)->toDateString();
            if (isset($porDia[$clave])) {
                $porDia[$clave][$f->tipo === 'entrada' ? 'entradas' : 'salidas'] = (int) $f->total;
            }
        }

        // ---- Top 5 productos con más salidas ----
        $topSalidas = Movimiento::query()
            ->where('tipo', 'salida')
            ->join('productos', 'productos.id', '=', 'movimientos.producto_id')
            ->select('productos.nombre', DB::raw('SUM(movimientos.cantidad) as total'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($r) => ['nombre' => $r->nombre, 'total' => (int) $r->total]);

        // ---- Productos en alerta ----
        $alertas = Producto::whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->limit(8)
            ->get(['id', 'codigo', 'nombre', 'stock', 'stock_minimo'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'codigo' => $p->codigo,
                'nombre' => $p->nombre,
                'stock' => (int) $p->stock,
                'stock_minimo' => (int) $p->stock_minimo,
                'estado' => $p->stock <= 0 ? 'agotado' : 'bajo',
            ]);

        // ---- Distribución de productos por categoría ----
        $porCategoria = Categoria::withCount('productos')
            ->orderByDesc('productos_count')
            ->get()
            ->map(fn ($c) => ['nombre' => $c->nombre, 'total' => $c->productos_count]);

        return response()->json([
            'indicadores' => $indicadores,
            'movimientos_por_dia' => array_values($porDia),
            'top_salidas' => $topSalidas,
            'alertas' => $alertas,
            'por_categoria' => $porCategoria,
        ]);
    }
}
