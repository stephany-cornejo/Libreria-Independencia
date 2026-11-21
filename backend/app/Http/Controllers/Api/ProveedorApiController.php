<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\GuardarProveedorRequest;
use Illuminate\Database\QueryException;

class ProveedorApiController extends Controller
{
    // Listar, buscar y filtrar proveedores
    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'in:activo,inactivo'],
        ]);

        $texto = trim($filtros['q'] ?? '');
        $estado = $filtros['estado'] ?? null;

        $proveedores = Proveedor::query()
            ->withCount('productos')
            ->when($texto !== '', function ($consulta) use ($texto) {
                $escapado = addcslashes($texto, '%_\\');

                $consulta->where(function ($subconsulta) use ($escapado) {
                    $subconsulta->where('nombre', 'like', "%{$escapado}%")
                        ->orWhere('telefono', 'like', "%{$escapado}%")
                        ->orWhere('email', 'like', "%{$escapado}%")
                        ->orWhere('direccion', 'like', "%{$escapado}%");
                });
            })
            ->when($estado !== null, function ($consulta) use ($estado) {
                $consulta->where('estado', $estado);
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return response()->json($proveedores);
    }

    // Consultar un proveedor individual
    public function show(Proveedor $proveedor): JsonResponse
    {
        return response()->json(
            $proveedor->loadCount('productos')
        );
    }


    // COMMIT 4: Registrar proveedor
    public function store(GuardarProveedorRequest $request): JsonResponse
    {
        $proveedor = Proveedor::create($request->validated());

        return response()->json([
            'mensaje' => 'Proveedor registrado correctamente.',
            'proveedor' => $proveedor->loadCount('productos'),
        ], 201);
    }

    // COMMIT 4: Editar proveedor
    public function update(
        GuardarProveedorRequest $request,
        Proveedor $proveedor
    ): JsonResponse {
        $proveedor->update($request->validated());

        return response()->json([
            'mensaje' => 'Proveedor actualizado correctamente.',
            'proveedor' => $proveedor->fresh()->loadCount('productos'),
        ]);
    }

    // COMMIT 4: Activar o desactivar proveedor
    public function updateStatus(
        Request $request,
        Proveedor $proveedor
    ): JsonResponse {
        $datos = $request->validate([
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        $proveedor->update($datos);

        return response()->json([
            'mensaje' => $proveedor->estado === 'activo'
                ? 'Proveedor activado correctamente.'
                : 'Proveedor desactivado correctamente.',
            'proveedor' => $proveedor->fresh()->loadCount('productos'),
        ]);
    }

    //Eliminar proveedor
    public function destroy(Proveedor $proveedor): JsonResponse
    {
        if ($proveedor->productos()->exists()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar: el proveedor tiene libros asociados.',
            ], 409);
        }

        try {
            $proveedor->delete();
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451) {
                throw $exception;
            }

            return response()->json([
                'mensaje' => 'No se puede eliminar: el proveedor tiene libros asociados.',
            ], 409);
        }

        return response()->json([
            'mensaje' => 'Proveedor eliminado correctamente.',
        ]);
    }

}
