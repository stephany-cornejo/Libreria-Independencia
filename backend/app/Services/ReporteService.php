<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    private function productos(): Builder
    {
        return DB::table('productos as p')
            ->leftJoin('categorias as c', 'c.id_categoria', '=', 'p.id_categoria');
    }

    public function existencias()
    {
        return $this->productos()
            ->select('p.codigo', 'p.nombre', 'c.nombre as categoria', 'p.cantidad_disponible', 'p.existencia_minima', 'p.estado')
            ->orderBy('p.nombre')
            ->get();
    }

    public function stockBajo()
    {
        return $this->productos()
            ->select('p.codigo', 'p.nombre', 'c.nombre as categoria', 'p.cantidad_disponible', 'p.existencia_minima')
            ->where('p.estado', 'activo')
            ->whereColumn('p.cantidad_disponible', '<=', 'p.existencia_minima')
            ->orderBy('p.cantidad_disponible')
            ->orderBy('p.nombre')
            ->get();
    }

    public function agotados()
    {
        return $this->productos()
            ->select('p.codigo', 'p.nombre', 'c.nombre as categoria', 'p.existencia_minima')
            ->where('p.estado', 'activo')
            ->where('p.cantidad_disponible', 0)
            ->orderBy('p.nombre')
            ->get();
    }

    public function masVendidos($desde, $hasta, int $limite = 10)
    {
        return DB::table('movimientos as m')
            ->join('productos as p', 'p.id_producto', '=', 'm.id_producto')
            ->where('m.tipo', 'salida')
            ->where('m.motivo', 'venta')
            ->whereBetween('m.fecha', $this->rango($desde, $hasta))
            ->groupBy('p.id_producto', 'p.codigo', 'p.nombre')
            ->selectRaw('p.codigo, p.nombre, SUM(m.cantidad) as total_vendido')
            ->orderByDesc('total_vendido')
            ->orderBy('p.nombre')
            ->limit($limite)
            ->get();
    }

    public function pocaRotacion($desde, $hasta, int $umbral = 2)
    {
        [$ini, $fin] = $this->rango($desde, $hasta);

        return DB::table('productos as p')
            ->leftJoin('movimientos as m', function ($join) use ($ini, $fin) {
                $join->on('m.id_producto', '=', 'p.id_producto')
                    ->whereBetween('m.fecha', [$ini, $fin]);
            })
            ->where('p.estado', 'activo')
            ->groupBy('p.id_producto', 'p.codigo', 'p.nombre', 'p.cantidad_disponible')
            ->havingRaw('COUNT(m.id_movimiento) <= ?', [$umbral])
            ->selectRaw('p.codigo, p.nombre, p.cantidad_disponible, COUNT(m.id_movimiento) as movimientos')
            ->orderBy('movimientos')
            ->orderBy('p.nombre')
            ->get();
    }

    public function movimientos($desde, $hasta)
    {
        return $this->consultaMovimientos(['desde' => $desde, 'hasta' => $hasta])->get();
    }

    public function historial(array $filtros, int $porPagina = 20)
    {
        return $this->consultaMovimientos($filtros)->paginate($porPagina)->withQueryString();
    }

    private function consultaMovimientos(array $f): Builder
    {
        return DB::table('movimientos as m')
            ->join('productos as p', 'p.id_producto', '=', 'm.id_producto')
            ->join('usuarios as u', 'u.id_usuario', '=', 'm.id_usuario')
            ->leftJoin('categorias as c', 'c.id_categoria', '=', 'p.id_categoria')
            ->select(
                'm.fecha', 'p.codigo', 'p.nombre as producto', 'c.nombre as categoria',
                'm.tipo', 'm.motivo', 'm.cantidad', 'm.existencia_resultante',
                'u.nombre as usuario', 'm.observaciones'
            )
            ->when($f['desde'] ?? null, fn ($q, $v) => $q->where('m.fecha', '>=', Carbon::parse($v)->startOfDay()))
            ->when($f['hasta'] ?? null, fn ($q, $v) => $q->where('m.fecha', '<=', Carbon::parse($v)->endOfDay()))
            ->when($f['id_producto'] ?? null, fn ($q, $v) => $q->where('m.id_producto', $v))
            ->when($f['tipo'] ?? null, fn ($q, $v) => $q->where('m.tipo', $v))
            ->when($f['id_usuario'] ?? null, fn ($q, $v) => $q->where('m.id_usuario', $v))
            ->when($f['id_categoria'] ?? null, fn ($q, $v) => $q->where('p.id_categoria', $v))
            ->orderByDesc('m.fecha')
            ->orderByDesc('m.id_movimiento');
    }

    private function rango($desde, $hasta): array
    {
        return [Carbon::parse($desde)->startOfDay(), Carbon::parse($hasta)->endOfDay()];
    }
}
