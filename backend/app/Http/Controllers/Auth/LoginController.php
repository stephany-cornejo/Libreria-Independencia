<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Valida las credenciales contra el modelo de usuarios,
     * comprueba que la cuenta esté activa y regenera la sesión
     * para impedir la fijación de sesión.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->validated())) {
            return back()
                ->withErrors([
                    'usuario' => 'Usuario o contraseña incorrectos.',
                ])
                ->onlyInput('usuario');
        }

        $usuario = Auth::user();

        if ($usuario->estado !== 'activo') {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'usuario' => 'La cuenta está inactiva. Contacta al administrador.',
                ])
                ->onlyInput('usuario');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }

    /**
     * Cierra la sesión en el servidor y limpia el estado del cliente.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
