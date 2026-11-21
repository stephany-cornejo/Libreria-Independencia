<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaApiController extends Controller
{
    // COMMIT 1: Listar, buscar y filtrar categorías
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

    // COMMIT 1: Consultar una categoría individual
    public function show(Categoria $categoria): JsonResponse
    {
        return response()->json(
            $categoria->loadCount('productos')
        );
    }

    // COMMIT 2: Registrar una categoría
    public function store(GuardarCategoriaRequest $request): JsonResponse
    {
        $categoria = Categoria::create($request->validated());

        return response()->json([
            'mensaje' => 'Categoría registrada correctamente.',
            'categoria' => $categoria->loadCount('productos'),
        ], 201);
    }

    // COMMIT 2: Editar una categoría
    public function update(
        GuardarCategoriaRequest $request,
        Categoria $categoria
    ): JsonResponse {
        $categoria->update($request->validated());

        return response()->json([
            'mensaje' => 'Categoría actualizada correctamente.',
            'categoria' => $categoria->fresh()->loadCount('productos'),
        ]);
    }

    // COMMIT 2: Activar o desactivar una categoría
    public function updateStatus(
        Request $request,
        Categoria $categoria
    ): JsonResponse {
        $datos = $request->validate([
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        $categoria->update($datos);

        return response()->json([
            'mensaje' => $categoria->estado === 'activo'
                ? 'Categoría activada correctamente.'
                : 'Categoría desactivada correctamente.',
            'categoria' => $categoria->fresh()->loadCount('productos'),
        ]);
    }

    // COMMIT 2: Eliminar una categoría
    public function destroy(Categoria $categoria): JsonResponse
    {
        if ($categoria->productos()->exists()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: la categoría tiene libros asociados.',
            ], 409);
        }

        try {
            $categoria->delete();
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451) {
                throw $exception;
            }

            return response()->json([
                'mensaje' => 'No se puede eliminar: la categoría tiene libros asociados.',
            ], 409);
        }

        return response()->json([
            'mensaje' => 'Categoría eliminada correctamente.',
        ]);
    }
}
