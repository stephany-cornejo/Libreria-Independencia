<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SesionController extends Controller
{
    /**
     * Devuelve los datos del usuario autenticado. Nunca incluye
     * el hash de la contraseña.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $usuario = $request->user()->load('rol');

        return response()->json([
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'usuario' => $usuario->usuario,
            'rol' => $usuario->rol->nombre,
            'estado' => $usuario->estado,
        ]);
    }
}
