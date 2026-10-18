<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Verifica que el usuario autenticado tenga uno de los roles
     * indicados. Ejemplo de uso: rol:Administrador
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if ($usuario === null) {
            abort(401, 'No autenticado.');
        }

        if (! in_array($usuario->rol?->nombre, $roles, true)) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        return $next($request);
    }
}
