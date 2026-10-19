<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function __construct(private ReporteService $servicio)
    {
    }

    public function index()
    {
        return view('reportes.index');
    }

    public function existencias()
    {
        return $this->tabla('Existencias actuales', [
            'codigo' => 'Código', 'nombre' => 'Producto', 'categoria' => 'Categoría',
            'cantidad_disponible' => 'Disponible', 'existencia_minima' => 'Mínimo', 'estado' => 'Estado',
        ], $this->servicio->existencias(), 'reportes.existencias');
    }

    public function stockBajo()
    {
        return $this->tabla('Productos con poca existencia', [
            'codigo' => 'Código', 'nombre' => 'Producto', 'categoria' => 'Categoría',
            'cantidad_disponible' => 'Disponible', 'existencia_minima' => 'Mínimo',
        ], $this->servicio->stockBajo(), 'reportes.stock-bajo', 'Ningún producto ha llegado a su existencia mínima.');
    }

    public function agotados()
    {
        return $this->tabla('Productos agotados', [
            'codigo' => 'Código', 'nombre' => 'Producto', 'categoria' => 'Categoría', 'existencia_minima' => 'Mínimo',
        ], $this->servicio->agotados(), 'reportes.agotados', 'No hay productos agotados.');
    }

    public function masVendidos(Request $request)
    {
        [$desde, $hasta] = $this->periodo($request);

        return $this->tabla('Productos más vendidos', [
            'codigo' => 'Código', 'nombre' => 'Producto', 'total_vendido' => 'Unidades vendidas',
        ], $this->servicio->masVendidos($desde, $hasta), 'reportes.mas-vendidos',
            'No hay ventas registradas en este período.', ['periodo' => [$desde, $hasta]]);
    }

    public function pocaRotacion(Request $request)
    {
        [$desde, $hasta] = $this->periodo($request);
        $umbral = (int) ($request->validate(['umbral' => ['nullable', 'integer', 'min:0', 'max:1000']])['umbral'] ?? 2);

        return $this->tabla('Productos con poca rotación', [
            'codigo' => 'Código', 'nombre' => 'Producto',
            'cantidad_disponible' => 'Disponible', 'movimientos' => 'Movimientos en el período',
        ], $this->servicio->pocaRotacion($desde, $hasta, $umbral), 'reportes.poca-rotacion',
            'Todos los productos superan ese número de movimientos.', ['periodo' => [$desde, $hasta], 'umbral' => $umbral]);
    }

    public function movimientos(Request $request)
    {
        [$desde, $hasta] = $this->periodo($request);

        return $this->tabla('Movimientos por período', [
            'fecha' => 'Fecha', 'codigo' => 'Código', 'producto' => 'Producto', 'tipo' => 'Tipo',
            'motivo' => 'Motivo', 'cantidad' => 'Cantidad', 'existencia_resultante' => 'Existencia resultante',
            'usuario' => 'Usuario',
        ], $this->servicio->movimientos($desde, $hasta), 'reportes.movimientos',
            'No hay movimientos entre esas fechas.', ['periodo' => [$desde, $hasta]]);
    }

    private function periodo(Request $request): array
    {
        $datos = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ], [
            'desde.date' => 'La fecha inicial no es válida.',
            'hasta.date' => 'La fecha final no es válida.',
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ]);

        return [
            Carbon::parse($datos['desde'] ?? now()->subDays(30))->toDateString(),
            Carbon::parse($datos['hasta'] ?? now())->toDateString(),
        ];
    }

    private function tabla(string $titulo, array $columnas, $filas, string $ruta, string $vacio = 'No hay datos para mostrar.', array $filtros = [])
    {
        return view('reportes.tabla', compact('titulo', 'columnas', 'filas', 'ruta', 'vacio', 'filtros'));
    }
}
