<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CuentaActiva
{
    /**
     * Cierra la sesión de un usuario cuya cuenta esté inactiva
     * y responde como no autenticado: JSON 401 en API o
     * redirección al login en vistas web.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario !== null && $usuario->estado !== 'activo') {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                abort(401, 'La cuenta está inactiva.');
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'usuario' => 'La cuenta está inactiva. Contacta al administrador.',
                ]);
        }

        return $next($request);
    }
}
