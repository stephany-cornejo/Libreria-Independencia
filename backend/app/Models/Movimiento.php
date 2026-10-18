<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimiento extends Model
{
    protected $table = 'movimientos';

    protected $primaryKey = 'id_movimiento';

    protected $fillable = [
        'tipo',
        'motivo',
        'id_producto',
        'id_usuario',
        'cantidad',
        'existencia_resultante',
        'fecha',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'id_producto' => 'integer',
            'id_usuario' => 'integer',
            'cantidad' => 'integer',
            'existencia_resultante' => 'integer',
            'fecha' => 'datetime',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(
            Producto::class,
            'id_producto',
            'id_producto'
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_usuario',
            'id_usuario'
        );
    }
}