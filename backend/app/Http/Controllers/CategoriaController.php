<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use App\Services\CategoriaService;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct(private CategoriaService $servicio)
    {
    }

    public function index(Request $request)
    {
        return view('categorias.index', [
            'categorias' => $this->servicio->listar($request->query('buscar')),
        ]);
    }

    public function create()
    {
        return view('categorias.form');
    }

    public function store(CategoriaRequest $request)
    {
        $this->servicio->crear($request->validated());

        return redirect()->route('categorias.index')->with('ok', 'Categoría creada.');
    }

    public function edit(Categoria $categoria)
    {
        return view('categorias.form', compact('categoria'));
    }

    public function update(CategoriaRequest $request, Categoria $categoria)
    {
        $this->servicio->actualizar($categoria, $request->validated());

        return redirect()->route('categorias.index')->with('ok', 'Categoría actualizada.');
    }

    public function estado(Categoria $categoria)
    {
        $categoria = $this->servicio->alternarEstado($categoria);

        return back()->with('ok', $categoria->estado === 'activo' ? 'Categoría activada.' : 'Categoría desactivada.');
    }
}
