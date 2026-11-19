<?php

namespace App\Services\Inventario;

use App\Events\StockBajo;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Servicio operativo de movimientos de inventario.
 *
 * Centraliza la única vía autorizada para modificar existencias:
 * cada movimiento se aplica dentro de una transacción que bloquea
 * la fila del producto (lockForUpdate), comprueba la disponibilidad
 * en las salidas y registra el movimiento con su existencia
 * resultante. Si algo falla, todo se revierte.
 */
class MovimientoService
{
    /**
     * Correspondencia autorizada entre tipo y motivo:
     * las entradas solo aceptan compra, reposición, devolución o
     * ajuste; las salidas, venta, pérdida, daño o ajuste.
     */
    private const MOTIVOS_POR_TIPO = [
        'entrada' => ['compra', 'reposicion', 'devolucion', 'ajuste'],
        'salida' => ['venta', 'perdida', 'dano', 'ajuste'],
    ];

    /**
     * Registra un movimiento y actualiza las existencias del producto
     * de forma atómica. Lanza RuntimeException con mensaje en español
     * si los datos no son aplicables. Al confirmarse la transacción,
     * si la existencia queda en el mínimo o por debajo se emite el
     * evento StockBajo (procesado en la cola).
     */
    public function registrar(array $datos, User $usuario): Movimiento
    {
        $this->validarTipoYMotivo($datos['tipo'], $datos['motivo']);

        $movimiento = DB::transaction(function () use ($datos, $usuario): Movimiento {
            /** @var Producto $producto */
            $producto = Producto::query()
                ->whereKey($datos['id_producto'])
                ->lockForUpdate()
                ->first();

            if (! $producto) {
                throw new RuntimeException('El libro seleccionado no existe.');
            }

            $cantidad = (int) $datos['cantidad'];

            if ($datos['tipo'] === 'salida' && $cantidad > $producto->cantidad_disponible) {
                throw new RuntimeException(
                    "Existencias insuficientes para la salida: el libro \"{$producto->nombre}\" solo tiene {$producto->cantidad_disponible} unidades disponibles."
                );
            }

            $nuevaExistencia = $datos['tipo'] === 'entrada'
                ? $producto->cantidad_disponible + $cantidad
                : $producto->cantidad_disponible - $cantidad;

            $producto->update(['cantidad_disponible' => $nuevaExistencia]);

            return Movimiento::create([
                'tipo' => $datos['tipo'],
                'motivo' => $datos['motivo'],
                'id_producto' => $producto->id_producto,
                'id_usuario' => $usuario->id_usuario,
                'cantidad' => $cantidad,
                'existencia_resultante' => $nuevaExistencia,
                'fecha' => now(),
                'observaciones' => $datos['observaciones'] ?? null,
            ]);
        });

        $this->avisarStockBajo($movimiento);

        return $movimiento;
    }

    /**
     * Emite StockBajo solo cuando el movimiento dejó el libro en su
     * mínimo o por debajo. Se emite después del commit para que el
     * listener lea datos confirmados.
     */
    private function avisarStockBajo(Movimiento $movimiento): void
    {
        $producto = $movimiento->producto;

        if ($producto !== null
            && $producto->cantidad_disponible <= $producto->existencia_minima
        ) {
            StockBajo::dispatch($producto);
        }
    }

    /**
     * Motivos permitidos por tipo, para el formulario y la validación.
     */
    public static function motivosPermitidos(string $tipo): array
    {
        return self::MOTIVOS_POR_TIPO[$tipo] ?? [];
    }

    private function validarTipoYMotivo(string $tipo, string $motivo): void
    {
        $permitidos = self::motivosPermitidos($tipo);

        if ($permitidos === []) {
            throw new RuntimeException('El tipo de movimiento no es válido.');
        }

        if (! in_array($motivo, $permitidos, true)) {
            throw new RuntimeException(
                "El motivo \"{$motivo}\" no corresponde a una {$tipo}."
            );
        }
    }
}
