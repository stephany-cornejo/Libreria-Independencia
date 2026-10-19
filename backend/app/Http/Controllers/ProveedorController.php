<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProveedorRequest;
use App\Models\Proveedor;
use App\Services\ProveedorService;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function __construct(private ProveedorService $servicio)
    {
    }

    public function index(Request $request)
    {
        return view('proveedores.index', [
            'proveedores' => $this->servicio->listar($request->query('buscar')),
        ]);
    }

    public function create()
    {
        return view('proveedores.form');
    }

    public function store(ProveedorRequest $request)
    {
        $this->servicio->crear($request->validated());

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor registrado.');
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.form', compact('proveedor'));
    }

    public function update(ProveedorRequest $request, Proveedor $proveedor)
    {
        $this->servicio->actualizar($proveedor, $request->validated());

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor actualizado.');
    }

    public function estado(Proveedor $proveedor)
    {
        $proveedor = $this->servicio->alternarEstado($proveedor);

        return back()->with('ok', $proveedor->estado === 'activo' ? 'Proveedor activado.' : 'Proveedor desactivado.');
    }
}
