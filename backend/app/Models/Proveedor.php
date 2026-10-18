<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $primaryKey = 'id_proveedor';

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'direccion',
        'estado',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(
            Producto::class,
            'id_proveedor',
            'id_proveedor'
        );
    }
}