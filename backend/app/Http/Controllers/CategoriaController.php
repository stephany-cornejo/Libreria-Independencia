<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class CategoriaController extends Controller
{
    /**
     * Listado de categorías con el número de libros asociados.
     */
    public function index(): View
    {
        return view('categorias.index', [
            'categorias' => Categoria::withCount('productos')
                ->orderBy('nombre')
                ->paginate(15),
        ]);
    }

    /**
     * Formulario para crear una categoría.
     */
    public function crear(): View
    {
        return view('categorias.crear');
    }

    /**
     * Valida y guarda una categoría nueva.
     */
    public function guardar(GuardarCategoriaRequest $request): RedirectResponse
    {
        Categoria::create($request->validated());

        return redirect()
            ->route('categorias.index')
            ->with('exito', 'Categoría registrada correctamente.');
    }

    /**
     * Formulario para editar una categoría existente.
     */
    public function editar(Categoria $categoria): View
    {
        return view('categorias.editar', [
            'categoria' => $categoria,
        ]);
    }

    /**
     * Actualiza los datos de la categoría.
     */
    public function actualizar(
        Categoria $categoria,
        GuardarCategoriaRequest $request
    ): RedirectResponse {
        $categoria->update($request->validated());

        return redirect()
            ->route('categorias.index')
            ->with('exito', 'Categoría actualizada correctamente.');
    }

    /**
     * Elimina una categoría. La base de datos impide borrar
     * categorías con libros asociados (integridad referencial):
     * en ese caso se devuelve un aviso claro.
     */
    public function eliminar(Categoria $categoria): RedirectResponse
    {
        try {
            $categoria->delete();
        } catch (QueryException $exception) {
            $esRestriccionDeClaveForanea = ($exception->errorInfo[1] ?? null) === 1451;

            if (! $esRestriccionDeClaveForanea) {
                throw $exception;
            }

            return back()->with(
                'error',
                'No se puede eliminar: la categoría tiene libros asociados.'
            );
        }

        return redirect()
            ->route('categorias.index')
            ->with('exito', 'Categoría eliminada correctamente.');
    }
}
