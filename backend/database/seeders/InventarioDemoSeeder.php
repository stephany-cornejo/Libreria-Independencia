<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventarioDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $usuario = User::where('usuario', 'admin.libreria')
                ->firstOrFail();

            $productos = [
                [
                    'codigo' => 'LIB-DEMO-001',
                    'nombre' => 'Libro de lectura demo',
                    'categoria' => 'Libros',
                    'proveedor' => 'Distribuidora Editorial Demo',
                    'precio_compra' => '6.00',
                    'precio_venta' => '9.50',
                    'cantidad' => 10,
                    'existencia_minima' => 3,
                ],
                [
                    'codigo' => 'UT-DEMO-001',
                    'nombre' => 'Cuaderno universitario demo',
                    'categoria' => 'Útiles escolares',
                    'proveedor' => 'Distribuidora Escolar Demo',
                    'precio_compra' => '1.20',
                    'precio_venta' => '2.00',
                    'cantidad' => 5,
                    'existencia_minima' => 5,
                ],
                [
                    'codigo' => 'OF-DEMO-001',
                    'nombre' => 'Marcador permanente demo',
                    'categoria' => 'Artículos de oficina',
                    'proveedor' => 'Distribuidora Escolar Demo',
                    'precio_compra' => '0.60',
                    'precio_venta' => '1.00',
                    'cantidad' => 2,
                    'existencia_minima' => 4,
                ],
            ];

            foreach ($productos as $datos) {
                $categoria = Categoria::where(
                    'nombre',
                    $datos['categoria']
                )->firstOrFail();

                $proveedor = Proveedor::where(
                    'nombre',
                    $datos['proveedor']
                )->firstOrFail();

                $producto = Producto::firstOrCreate(
                    ['codigo' => $datos['codigo']],
                    [
                        'nombre' => $datos['nombre'],
                        'id_categoria' => $categoria->id_categoria,
                        'id_proveedor' => $proveedor->id_proveedor,
                        'precio_compra' => $datos['precio_compra'],
                        'precio_venta' => $datos['precio_venta'],
                        'cantidad_disponible' => $datos['cantidad'],
                        'existencia_minima' => $datos['existencia_minima'],
                        'fecha_ingreso' => today()->toDateString(),
                        'estado' => 'activo',
                    ]
                );

                if (!$producto->wasRecentlyCreated) {
                    continue;
                }

                Movimiento::create([
                    'tipo' => 'entrada',
                    'motivo' => 'compra',
                    'id_producto' => $producto->id_producto,
                    'id_usuario' => $usuario->id_usuario,
                    'cantidad' => $datos['cantidad'],
                    'existencia_resultante' => $datos['cantidad'],
                    'fecha' => now(),
                    'observaciones' => 'Carga inicial de demostración.',
                ]);
            }
        });
    }
}