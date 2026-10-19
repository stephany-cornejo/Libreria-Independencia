<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Proveedor;

class ProveedorService
{
    public function listar(?string $buscar = null, int $porPagina = 15)
    {
        return Proveedor::query()
            ->select('proveedores.*')
            ->addSelect([
                'productos_count' => Producto::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('productos.id_proveedor', 'proveedores.id_proveedor'),
            ])
            ->when($buscar, fn ($q, $texto) => $q->where('nombre', 'like', "%{$texto}%"))
            ->orderBy('nombre')
            ->paginate($porPagina)
            ->withQueryString();
    }

    public function activos()
    {
        return Proveedor::where('estado', 'activo')->orderBy('nombre')->get();
    }

    public function crear(array $datos): Proveedor
    {
        return Proveedor::create($datos);
    }

    public function actualizar(Proveedor $proveedor, array $datos): Proveedor
    {
        $proveedor->update($datos);

        return $proveedor;
    }

    public function alternarEstado(Proveedor $proveedor): Proveedor
    {
        $proveedor->estado = $proveedor->estado === 'inactivo' ? 'activo' : 'inactivo';
        $proveedor->save();

        return $proveedor;
    }
}
