<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    /**
     * Panel principal accesible para cualquier usuario
     * autenticado con cuenta activa.
     */
    public function index(): View
    {
        return view('panel.index', [
            'usuario' => Auth::user()->load('rol'),
        ]);
    }

    /**
     * Configuración del sistema, reservada al rol Administrador.
     */
    public function configuracion(): View
    {
        return view('panel.configuracion', [
            'usuario' => Auth::user()->load('rol'),
        ]);
    }
}
