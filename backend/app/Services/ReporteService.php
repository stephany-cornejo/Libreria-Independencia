<?php

namespace App\Services;

use App\Models\Movimiento;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Lógica única de consulta para reportes. La usan las vistas JSON, los PDF y los Excel,
 * así los tres formatos siempre muestran exactamente los mismos datos.
 */
class ReporteService
{
    public function inventario(Request $request): Collection
    {
        return Producto::with(['categoria:id,nombre', 'proveedor:id,nombre'])
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $b = $request->input('buscar');
                $q->where(fn ($q) => $q->where('nombre', 'like', "%{$b}%")
                    ->orWhere('codigo', 'like', "%{$b}%"));
            })
            ->when($request->filled('categoria_id'), fn ($q) => $q->where('categoria_id', $request->categoria_id))
            ->when($request->filled('proveedor_id'), fn ($q) => $q->where('proveedor_id', $request->proveedor_id))
            ->when($request->input('estado') === 'agotado', fn ($q) => $q->where('stock', 0))
            ->when($request->input('estado') === 'bajo', fn ($q) => $q->where('stock', '>', 0)
                ->whereColumn('stock', '<=', 'stock_minimo'))
            ->when($request->input('estado') === 'normal', fn ($q) => $q->whereColumn('stock', '>', 'stock_minimo'))
            ->orderBy('nombre')
            ->get()
            ->map(function (Producto $p) {
                return [
                    'codigo' => $p->codigo,
                    'nombre' => $p->nombre,
                    'categoria' => $p->categoria?->nombre,
                    'proveedor' => $p->proveedor?->nombre,
                    'stock' => (int) $p->stock,
                    'stock_minimo' => (int) $p->stock_minimo,
                    'precio_compra' => (float) $p->precio_compra,
                    'valor_inventario' => round($p->stock * (float) $p->precio_compra, 2),
                    'estado' => $this->estadoStock($p->stock, $p->stock_minimo),
                ];
            });
    }

    public function movimientos(Request $request): Collection
    {
        return Movimiento::with(['producto:id,codigo,nombre', 'usuario:id,name'])
            ->when($request->filled('desde'), fn ($q) => $q->where('fecha_movimiento', '>=', $request->desde.' 00:00:00'))
            ->when($request->filled('hasta'), fn ($q) => $q->where('fecha_movimiento', '<=', $request->hasta.' 23:59:59'))
            ->when($request->filled('producto_id'), fn ($q) => $q->where('producto_id', $request->producto_id))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->orderByDesc('fecha_movimiento')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Movimiento $m) => [
                'fecha' => $m->fecha_movimiento->format('Y-m-d H:i'),
                'codigo' => $m->producto?->codigo,
                'producto' => $m->producto?->nombre,
                'tipo' => $m->tipo,
                'cantidad' => (int) $m->cantidad,
                'motivo' => $m->motivo,
                'usuario' => $m->usuario?->name,
            ]);
    }

    public function resumenMovimientos(Collection $filas): array
    {
        return [
            'total_movimientos' => $filas->count(),
            'total_entradas' => (int) $filas->where('tipo', 'entrada')->sum('cantidad'),
            'total_salidas' => (int) $filas->where('tipo', 'salida')->sum('cantidad'),
        ];
    }

    public function resumenInventario(Collection $filas): array
    {
        return [
            'total_productos' => $filas->count(),
            'total_unidades' => (int) $filas->sum('stock'),
            'valor_total' => round($filas->sum('valor_inventario'), 2),
            'agotados' => $filas->where('estado', 'agotado')->count(),
            'bajos' => $filas->where('estado', 'bajo')->count(),
        ];
    }

    public function estadoStock(int $stock, int $minimo): string
    {
        if ($stock <= 0) {
            return 'agotado';
        }

        return $stock <= $minimo ? 'bajo' : 'normal';
    }
}
