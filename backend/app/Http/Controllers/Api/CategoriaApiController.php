<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'in:activo,inactivo'],
        ]);

        $texto = trim($filtros['q'] ?? '');
        $estado = $filtros['estado'] ?? null;

        $categorias = Categoria::query()
            ->withCount('productos')
            ->when($texto !== '', function ($consulta) use ($texto) {
                $escapado = addcslashes($texto, '%_\\');
                $consulta->where('nombre', 'like', "%{$escapado}%");
            })
            ->when($estado !== null, function ($consulta) use ($estado) {
                $consulta->where('estado', $estado);
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return response()->json($categorias);
    }

    public function show(Categoria $categoria): JsonResponse
    {
        return response()->json(
            $categoria->loadCount('productos')
        );
    }
}
