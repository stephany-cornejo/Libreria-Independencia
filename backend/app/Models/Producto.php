<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'autor',
        'editorial',
        'isbn',
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

    /**
     * Estado de existencias del libro para las alertas del inventario:
     * 'agotado' (0 unidades), 'bajo' (até o por debajo del mínimo)
     * o 'disponible' (por encima del mínimo).
     */
    public function estadoStock(): string
    {
        if ($this->cantidad_disponible <= 0) {
            return 'agotado';
        }

        if ($this->cantidad_disponible <= $this->existencia_minima) {
            return 'bajo';
        }

        return 'disponible';
    }

    /**
     * Filtra por texto libre en título o autor (coincidencia parcial,
     * escapando los comodines % y _ de SQL LIKE).
     */
    public function scopeTexto(Builder $query, string $texto): Builder
    {
        $escapado = addcslashes($texto, '%_\\');

        return $query->where(function (Builder $sub) use ($escapado): void {
            $sub->where('nombre', 'like', "%{$escapado}%")
                ->orWhere('autor', 'like', "%{$escapado}%");
        });
    }

    /**
     * Filtra por disponibilidad usando los mismos criterios que
     * estadoStock() para que la lista y las alertas coincidan.
     */
    public function scopeDisponibilidad(
        Builder $query,
        string $disponibilidad
    ): Builder {
        return match ($disponibilidad) {
            'agotado' => $query->where('cantidad_disponible', 0),
            'bajo' => $query
                ->where('cantidad_disponible', '>', 0)
                ->whereColumn(
                    'cantidad_disponible',
                    '<=',
                    'existencia_minima'
                ),
            'disponible' => $query
                ->whereColumn('cantidad_disponible', '>', 'existencia_minima'),
            default => $query,
        };
    }
}
