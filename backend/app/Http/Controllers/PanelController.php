<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    /**
     * Panel principal accesible para cualquier usuario
     * autenticado con cuenta activa. Muestra un resumen real
     * del inventario, los movimientos y las cuentas de acceso.
     */
    public function index(): View
    {
        $usuario = Auth::user()->load('rol');
        $esAdministrador = $usuario->rol->nombre === 'Administrador';

        return view('panel.index', [
            'usuario' => $usuario,
            'esAdministrador' => $esAdministrador,
            'estadisticas' => $this->estadisticas($esAdministrador),
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

    /**
     * Métricas reales del sistema. Los contadores de libros se limitan
     * a los libros activos (los desactivados no generan alertas) y se
     * evita cargar tablas completas.
     */
    private function estadisticas(bool $esAdministrador): array
    {
        $productos = Producto::query()->where('estado', 'activo');

        return [
            [
                'etiqueta' => 'Libros en catálogo',
                'valor' => (clone $productos)->count(),
                'icono' => 'libros',
                'tono' => 'indigo',
                'enlace' => route('inventario.index', ['estado' => 'activo']),
                'detalle' => 'Ver inventario completo',
            ],
            [
                'etiqueta' => 'Con existencias',
                'valor' => (clone $productos)
                    ->where('cantidad_disponible', '>', 0)
                    ->count(),
                'icono' => 'disponible',
                'tono' => 'esmeralda',
                'enlace' => route('inventario.index', ['estado' => 'activo']),
                'detalle' => 'Unidades disponibles',
            ],
            [
                'etiqueta' => 'Stock bajo o agotado',
                'valor' => (clone $productos)
                    ->whereColumn('cantidad_disponible', '<=', 'existencia_minima')
                    ->count(),
                'icono' => 'alerta',
                'tono' => 'ambar',
                'enlace' => route('inventario.index', [
                    'estado' => 'activo',
                    'disponibilidad' => 'alerta',
                ]),
                'detalle' => 'Requieren reposición',
            ],
            [
                'etiqueta' => 'Movimientos registrados',
                'valor' => Movimiento::query()->count(),
                'icono' => 'movimientos',
                'tono' => 'violeta',
                'enlace' => route('movimientos.index'),
                'detalle' => 'Entradas y salidas',
            ],
            $esAdministrador ? [
                'etiqueta' => 'Cuentas de acceso',
                'valor' => User::query()->count(),
                'icono' => 'usuarios',
                'tono' => 'rosa',
                'enlace' => route('usuarios.index'),
                'detalle' => User::query()
                    ->where('estado', 'activo')
                    ->count().' activas',
            ] : [
                'etiqueta' => 'Categorías',
                'valor' => Categoria::query()->count(),
                'icono' => 'categorias',
                'tono' => 'rosa',
                'enlace' => route('inventario.index'),
                'detalle' => 'Temas del catálogo',
            ],
        ];
    }
}