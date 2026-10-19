<?php

namespace App\Listeners;

use App\Events\StockBajo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class RegistrarAlertaStockBajo implements ShouldQueue
{
    public function handle(StockBajo $evento): void
    {
        $p = $evento->producto;

        Log::warning('Producto con existencia baja', [
            'codigo' => $p->codigo,
            'nombre' => $p->nombre,
            'disponible' => $p->cantidad_disponible,
            'minimo' => $p->existencia_minima,
        ]);
    }
}
