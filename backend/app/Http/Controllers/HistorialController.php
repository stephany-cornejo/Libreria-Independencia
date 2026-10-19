<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistorialController extends Controller
{
    public function __construct(private ReporteService $servicio)
    {
    }

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'id_producto' => ['nullable', 'integer'],
            'tipo' => ['nullable', 'in:entrada,salida'],
            'id_usuario' => ['nullable', 'integer'],
            'id_categoria' => ['nullable', 'integer'],
        ], [
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ]);

        $esAdmin = Rol::find($request->user()->id_rol)?->nombre === 'Administrador';

        if (! $esAdmin) {
            $filtros['id_usuario'] = $request->user()->getKey();
        }

        return view('historial.index', [
            'movimientos' => $this->servicio->historial($filtros),
            'productos' => DB::table('productos')->select('id_producto', 'nombre')->orderBy('nombre')->get(),
            'categorias' => DB::table('categorias')->select('id_categoria', 'nombre')->orderBy('nombre')->get(),
            'usuarios' => $esAdmin ? DB::table('usuarios')->select('id_usuario', 'nombre')->orderBy('nombre')->get() : collect(),
            'esAdmin' => $esAdmin,
        ]);
    }
}
