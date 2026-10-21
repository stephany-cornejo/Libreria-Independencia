<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class ProveedorController extends Controller
{
    /**
     * Listado de proveedores con el número de libros asociados.
     */
    public function index(): View
    {
        return view('proveedores.index', [
            'proveedores' => Proveedor::withCount('productos')
                ->orderBy('nombre')
                ->paginate(15),
        ]);
    }

    /**
     * Formulario para crear un proveedor.
     */
    public function crear(): View
    {
        return view('proveedores.crear');
    }

    /**
     * Valida y guarda un proveedor nuevo.
     */
    public function guardar(GuardarProveedorRequest $request): RedirectResponse
    {
        Proveedor::create($request->validated());

        return redirect()
            ->route('proveedores.index')
            ->with('exito', 'Proveedor registrado correctamente.');
    }

    /**
     * Formulario para editar un proveedor existente.
     */
    public function editar(Proveedor $proveedor): View
    {
        return view('proveedores.editar', [
            'proveedor' => $proveedor,
        ]);
    }

    /**
     * Actualiza los datos del proveedor.
     */
    public function actualizar(
        Proveedor $proveedor,
        GuardarProveedorRequest $request
    ): RedirectResponse {
        $proveedor->update($request->validated());

        return redirect()
            ->route('proveedores.index')
            ->with('exito', 'Proveedor actualizado correctamente.');
    }

    /**
     * Elimina un proveedor. La base de datos impide borrar
     * proveedores con libros asociados (integridad referencial):
     * en ese caso se devuelve un aviso claro.
     */
    public function eliminar(Proveedor $proveedor): RedirectResponse
    {
        try {
            $proveedor->delete();
        } catch (QueryException $exception) {
            $esRestriccionDeClaveForanea = ($exception->errorInfo[1] ?? null) === 1451;

            if (! $esRestriccionDeClaveForanea) {
                throw $exception;
            }

            return back()->with(
                'error',
                'No se puede eliminar: el proveedor tiene libros asociados.'
            );
        }

        return redirect()
            ->route('proveedores.index')
            ->with('exito', 'Proveedor eliminado correctamente.');
    }
}
