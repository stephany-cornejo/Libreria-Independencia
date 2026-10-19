<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Producto;

class CategoriaService
{
    public function listar(?string $buscar = null, int $porPagina = 15)
    {
        return Categoria::query()
            ->select('categorias.*')
            ->addSelect([
                'productos_count' => Producto::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('productos.id_categoria', 'categorias.id_categoria'),
            ])
            ->when($buscar, fn ($q, $texto) => $q->where('nombre', 'like', "%{$texto}%"))
            ->orderBy('nombre')
            ->paginate($porPagina)
            ->withQueryString();
    }

    public function activas()
    {
        return Categoria::where('estado', 'activo')->orderBy('nombre')->get();
    }

    public function crear(array $datos): Categoria
    {
        return Categoria::create($datos);
    }

    public function actualizar(Categoria $categoria, array $datos): Categoria
    {
        $categoria->update($datos);

        return $categoria;
    }

    public function alternarEstado(Categoria $categoria): Categoria
    {
        $categoria->estado = $categoria->estado === 'inactivo' ? 'activo' : 'inactivo';
        $categoria->save();

        return $categoria;
    }
}
