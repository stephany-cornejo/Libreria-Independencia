<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
