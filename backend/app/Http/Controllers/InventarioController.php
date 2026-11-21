<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarLibroRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventarioController extends Controller
{
    /**
     * Listado del inventario con búsqueda por título, autor, código o
     * ISBN, filtros por categoría, proveedor, estado y disponibilidad,
     * más el resumen de existencias (títulos, unidades, valor y alertas
     * de stock).
     */
    public function index(Request $request): View
    {
        $texto = trim((string) $request->query('q', ''));
        $categoria = is_numeric($request->query('categoria'))
            ? (int) $request->query('categoria')
            : null;
        $proveedor = is_numeric($request->query('proveedor'))
            ? (int) $request->query('proveedor')
            : null;

        $disponibilidades = ['disponible', 'bajo', 'agotado'];
        $disponibilidad = in_array(
            $request->query('disponibilidad'),
            $disponibilidades,
            true
        ) ? $request->query('disponibilidad') : null;

        $estados = ['activo', 'desactivado'];
        $estado = in_array(
            $request->query('estado'),
            $estados,
            true
        ) ? $request->query('estado') : null;

        $productos = Producto::query()
            ->with('categoria:id_categoria,nombre')
            ->when($texto !== '', fn ($query) => $query->texto($texto))
            ->when($categoria !== null, fn ($query) => $query->where('id_categoria', $categoria))
            ->when($proveedor !== null, fn ($query) => $query->where('id_proveedor', $proveedor))
            ->when($estado !== null, fn ($query) => $query->estado($estado))
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
            'proveedores' => Proveedor::orderBy('nombre')
                ->get(['id_proveedor', 'nombre']),
            'resumen' => $resumen,
            'filtros' => [
                'q' => $texto,
                'categoria' => $categoria,
                'proveedor' => $proveedor,
                'estado' => $estado,
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
            'proveedores' => $this->proveedoresParaFormulario(),
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
            'id_proveedor' => $datos['id_proveedor'] ?? null,
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
            'proveedores' => $this->proveedoresParaFormulario($producto->id_proveedor),
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
            'id_proveedor' => $datos['id_proveedor'] ?? null,
            'precio_venta' => $datos['precio'],
            'cantidad_disponible' => $datos['cantidad'],
        ]);

        return redirect()
            ->route('inventario.index')
            ->with('exito', 'Libro actualizado correctamente.');
    }

    /**
     * Desactiva un libro (estado "descontinuado"). No se borra: conserva
     * su historial de movimientos y deja de aceptar nuevos movimientos.
     */
    public function desactivar(Producto $producto): RedirectResponse
    {
        $producto->update(['estado' => 'descontinuado']);

        return redirect()
            ->route('inventario.index')
            ->with('exito', 'Libro desactivado correctamente.');
    }

    /**
     * Vuelve a activar un libro desactivado.
     */
    public function reactivar(Producto $producto): RedirectResponse
    {
        $producto->update(['estado' => 'activo']);

        return redirect()
            ->route('inventario.index')
            ->with('exito', 'Libro reactivado correctamente.');
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

    /**
     * Proveedores activos para el formulario. En edición se incluye
     * también el proveedor actual del libro aunque esté inactivo,
     * para no perder la relación existente.
     */
    private function proveedoresParaFormulario(?int $actual = null)
    {
        return Proveedor::query()
            ->where('estado', 'activo')
            ->when($actual !== null, fn ($query) => $query->orWhere('id_proveedor', $actual))
            ->orderBy('nombre')
            ->get(['id_proveedor', 'nombre']);
    }
}