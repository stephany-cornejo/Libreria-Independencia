<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'id_categoria',
        'id_proveedor',
        'precio_compra',
        'precio_venta',
        'cantidad_disponible',
        'existencia_minima',
        'fecha_ingreso',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'id_categoria' => 'integer',
            'id_proveedor' => 'integer',
            'precio_compra' => 'decimal:2',
            'precio_venta' => 'decimal:2',
            'cantidad_disponible' => 'integer',
            'existencia_minima' => 'integer',
            'fecha_ingreso' => 'date',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(
            Categoria::class,
            'id_categoria',
            'id_categoria'
        );
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(
            Proveedor::class,
            'id_proveedor',
            'id_proveedor'
        );
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(
            Movimiento::class,
            'id_producto',
            'id_producto'
        );
    }
}