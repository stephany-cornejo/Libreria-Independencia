<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarLibroRequest;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventarioController extends Controller
{
    /**
     * Listado del inventario con búsqueda por título/autor, filtro
     * por categoría y disponibilidad, más el resumen de existencias
     * (títulos, unidades, valor y alertas de stock).
     */
    public function index(Request $request): View
    {
        $texto = trim((string) $request->query('q', ''));
        $categoria = is_numeric($request->query('categoria'))
            ? (int) $request->query('categoria')
            : null;

        $disponibilidades = ['disponible', 'bajo', 'agotado'];
        $disponibilidad = in_array(
            $request->query('disponibilidad'),
            $disponibilidades,
            true
        ) ? $request->query('disponibilidad') : null;

        $productos = Producto::query()
            ->with('categoria:id_categoria,nombre')
            ->when($texto !== '', fn ($query) => $query->texto($texto))
            ->when($categoria !== null, fn ($query) => $query->where('id_categoria', $categoria))
            ->when($disponibilidad !== null, fn ($query) => $query->disponibilidad($disponibilidad))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        // Resumen global del inventario, independiente de los filtros.
        $resumen = DB::table('productos')->selectRaw('
                COUNT(*) AS titulos,
                COALESCE(SUM(cantidad_disponible), 0) AS unidades,
                COALESCE(SUM(cantidad_disponible * precio_venta), 0) AS valor,
                COALESCE(SUM(cantidad_disponible = 0), 0) AS agotados,
                COALESCE(SUM(cantidad_disponible > 0 AND cantidad_disponible <= existencia_minima), 0) AS bajos
            ')->first();

        return view('inventario.index', [
            'productos' => $productos,
            'categorias' => Categoria::where('estado', 'activo')
                ->orderBy('nombre')
                ->get(),
            'resumen' => $resumen,
            'filtros' => [
                'q' => $texto,
                'categoria' => $categoria,
                'disponibilidad' => $disponibilidad,
            ],
        ]);
    }

    /**
     * Formulario para registrar un libro nuevo.
     */
    public function crear(): View
    {
        return view('inventario.crear', [
            'categorias' => Categoria::where('estado', 'activo')
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    /**
     * Valida y guarda un libro nuevo con un código interno único
     * generado automáticamente y un mínimo de existencias de 5
     * unidades para las alertas de stock bajo.
     */
    public function guardar(GuardarLibroRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        Producto::create([
            'codigo' => $this->generarCodigo(),
            'nombre' => $datos['nombre'],
            'autor' => $datos['autor'] ?? null,
            'editorial' => $datos['editorial'] ?? null,
            'isbn' => $datos['isbn'] ?? null,
            'id_categoria' => $datos['id_categoria'],
            'id_proveedor' => null,
            'precio_compra' => 0,
            'precio_venta' => $datos['precio'],
            'cantidad_disponible' => $datos['cantidad'],
            'existencia_minima' => 5,
            'fecha_ingreso' => today()->toDateString(),
            'estado' => 'activo',
        ]);

        return redirect()
            ->route('inventario.index')
            ->with('exito', 'Libro registrado correctamente.');
    }

    /**
     * Formulario para editar un libro existente.
     */
    public function editar(Producto $producto): View
    {
        return view('inventario.editar', [
            'producto' => $producto,
            'categorias' => Categoria::where('estado', 'activo')
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    /**
     * Actualiza los datos del libro con la misma validación
     * que el registro.
     */
    public function actualizar(
        Producto $producto,
        GuardarLibroRequest $request
    ): RedirectResponse {
        $datos = $request->validated();

        $producto->update([
            'nombre' => $datos['nombre'],
            'autor' => $datos['autor'] ?? null,
            'editorial' => $datos['editorial'] ?? null,
            'isbn' => $datos['isbn'] ?? null,
            'id_categoria' => $datos['id_categoria'],
            'precio_venta' => $datos['precio'],
            'cantidad_disponible' => $datos['cantidad'],
        ]);

        return redirect()
            ->route('inventario.index')
            ->with('exito', 'Libro actualizado correctamente.');
    }

    /**
     * Elimina un libro. La base de datos impide borrar títulos con
     * movimientos registrados (integridad referencial): en ese caso
     * se devuelve un aviso claro en lugar de un error técnico.
     */
    public function eliminar(Producto $producto): RedirectResponse
    {
        try {
            $producto->delete();
        } catch (QueryException $exception) {
            $esRestriccionDeClaveForanea = ($exception->errorInfo[1] ?? null) === 1451;

            if (! $esRestriccionDeClaveForanea) {
                throw $exception;
            }

            return back()->with(
                'error',
                'No se puede eliminar: el libro tiene movimientos de inventario registrados.'
            );
        }

        return redirect()
            ->route('inventario.index')
            ->with('exito', 'Libro eliminado correctamente.');
    }

    /**
     * Genera un código interno único con el formato LIB-XXXXXX.
     */
    private function generarCodigo(): string
    {
        do {
            $codigo = 'LIB-'.strtoupper(Str::random(6));
        } while (Producto::where('codigo', $codigo)->exists());

        return $codigo;
    }
}
