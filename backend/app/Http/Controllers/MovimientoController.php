<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrarMovimientoRequest;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Services\Inventario\MovimientoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MovimientoController extends Controller
{
    /**
     * Historial de movimientos con filtros por tipo, texto en el
     * libro y paginación. El historial es de solo lectura: las
     * correcciones se hacen con nuevos movimientos de ajuste.
     */
    public function index(Request $request): View
    {
        $texto = trim((string) $request->query('q', ''));

        $tipos = ['entrada', 'salida'];
        $tipo = in_array($request->query('tipo'), $tipos, true)
            ? $request->query('tipo')
            : null;

        $movimientos = Movimiento::query()
            ->with([
                'producto:id_producto,nombre,codigo',
                'usuario:id_usuario,nombre',
            ])
            ->when($tipo !== null, fn ($query) => $query->where('tipo', $tipo))
            ->when($texto !== '', function ($query) use ($texto): void {
                $escapado = addcslashes($texto, '%_\\');
                $query->whereHas('producto', function ($sub) use ($escapado): void {
                    $sub->where('nombre', 'like', "%{$escapado}%")
                        ->orWhere('codigo', 'like', "%{$escapado}%");
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id_movimiento')
            ->paginate(15)
            ->withQueryString();

        return view('movimientos.index', [
            'movimientos' => $movimientos,
            'filtros' => [
                'q' => $texto,
                'tipo' => $tipo,
            ],
        ]);
    }

    /**
     * Formulario para registrar una entrada o salida de inventario.
     */
    public function crear(): View
    {
        return view('movimientos.crear', [
            'productos' => Producto::where('estado', 'activo')
                ->orderBy('nombre')
                ->get(['id_producto', 'nombre', 'codigo', 'cantidad_disponible']),
            'motivosPorTipo' => $this->motivosEtiquetados(),
        ]);
    }

    /**
     * Valida y aplica el movimiento a través del servicio
     * transaccional. Los errores de negocio (stock insuficiente,
     * libro inexistente) vuelven al formulario como aviso claro.
     */
    public function guardar(
        RegistrarMovimientoRequest $request,
        MovimientoService $servicio
    ): RedirectResponse {
        try {
            $movimiento = $servicio->registrar(
                $request->validated(),
                $request->user()
            );
        } catch (RuntimeException $exception) {
            return back()
                ->with('error', $exception->getMessage())
                ->withInput();
        }

        return redirect()
            ->route('movimientos.index')
            ->with('exito', "Movimiento registrado. Existencia resultante: {$movimiento->existencia_resultante} unidades.");
    }

    /**
     * Etiquetas en español de cada motivo, agrupadas por tipo,
     * para poblar el formulario.
     */
    private function motivosEtiquetados(): array
    {
        $etiquetas = [
            'compra' => 'Compra',
            'reposicion' => 'Reposición',
            'devolucion' => 'Devolución',
            'venta' => 'Venta',
            'perdida' => 'Pérdida',
            'dano' => 'Daño',
            'ajuste' => 'Ajuste',
        ];

        return collect(['entrada', 'salida'])
            ->mapWithKeys(fn (string $tipo): array => [
                $tipo => collect(MovimientoService::motivosPermitidos($tipo))
                    ->mapWithKeys(fn (string $motivo): array => [
                        $motivo => $etiquetas[$motivo] ?? $motivo,
                    ])
                    ->all(),
            ])
            ->all();
    }
}
