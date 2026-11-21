<?php

namespace App\Listeners;

use App\Events\StockBajo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Registra la alerta de existencias bajas en el log. Implementa
 * ShouldQueue para procesarse de forma asíncrona cuando la cola
 * no es "sync" (con QUEUE_CONNECTION=database se encola en la
 * tabla jobs).
 */
class RegistrarAlertaStockBajo implements ShouldQueue
{
    public function handle(StockBajo $evento): void
    {
        $producto = $evento->producto;

        Log::warning('Producto con existencia baja', [
            'codigo' => $producto->codigo,
            'nombre' => $producto->nombre,
            'disponible' => $producto->cantidad_disponible,
            'minimo' => $producto->existencia_minima,
        ]);
    }
}
