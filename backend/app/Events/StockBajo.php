<?php

namespace App\Events;

use App\Models\Producto;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se dispara cuando un movimiento deja un libro con existencias
 * iguales o por debajo de su mínimo. El listener asociado se
 * ejecuta en la cola (operación asíncrona) para no retrasar el
 * registro del movimiento.
 */
class StockBajo
{
    use Dispatchable, SerializesModels;

    public function __construct(public Producto $producto)
    {
    }
}
