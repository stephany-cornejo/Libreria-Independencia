<?php

namespace App\Http\Controllers;

use App\Exports\InventarioExport;
use App\Exports\MovimientosExport;
use App\Services\ReporteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function __construct(private ReporteService $servicio)
    {
    }

    private function validar(Request $request): void
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'tipo' => ['nullable', 'in:entrada,salida'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'estado' => ['nullable', 'in:agotado,bajo,normal'],
        ], [
            'hasta.after_or_equal' => 'La fecha final no puede ser menor que la inicial.',
            'tipo.in' => 'El tipo debe ser entrada o salida.',
        ]);
    }

    // ---------- Inventario ----------
    public function inventario(Request $request): JsonResponse
    {
        $this->validar($request);
        $filas = $this->servicio->inventario($request);

        return response()->json([
            'resumen' => $this->servicio->resumenInventario($filas),
            'filas' => $filas->values(),
        ]);
    }

    public function inventarioPdf(Request $request)
    {
        $this->validar($request);
        $filas = $this->servicio->inventario($request);

        return Pdf::loadView('reportes.inventario', [
            'filas' => $filas,
            'resumen' => $this->servicio->resumenInventario($filas),
            'generado' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape')->download('reporte-inventario.pdf');
    }

    public function inventarioExcel(Request $request)
    {
        $this->validar($request);

        return Excel::download(
            new InventarioExport($this->servicio->inventario($request)),
            'reporte-inventario.xlsx'
        );
    }

    // ---------- Movimientos (entradas y salidas) ----------
    public function movimientos(Request $request): JsonResponse
    {
        $this->validar($request);
        $filas = $this->servicio->movimientos($request);

        return response()->json([
            'resumen' => $this->servicio->resumenMovimientos($filas),
            'filas' => $filas->values(),
        ]);
    }

    public function movimientosPdf(Request $request)
    {
        $this->validar($request);
        $filas = $this->servicio->movimientos($request);

        return Pdf::loadView('reportes.movimientos', [
            'filas' => $filas,
            'resumen' => $this->servicio->resumenMovimientos($filas),
            'filtros' => $request->only(['desde', 'hasta', 'tipo']),
            'generado' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape')->download('reporte-movimientos.pdf');
    }

    public function movimientosExcel(Request $request)
    {
        $this->validar($request);

        return Excel::download(
            new MovimientosExport($this->servicio->movimientos($request)),
            'reporte-movimientos.xlsx'
        );
    }
}
