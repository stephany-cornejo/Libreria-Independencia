<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Libros',
                'descripcion' => 'Libros de lectura y consulta.',
            ],
            [
                'nombre' => 'Útiles escolares',
                'descripcion' => 'Materiales para actividades escolares.',
            ],
            [
                'nombre' => 'Artículos de oficina',
                'descripcion' => 'Materiales para trabajo de oficina.',
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(
                ['nombre' => $categoria['nombre']],
                [
                    'descripcion' => $categoria['descripcion'],
                    'estado' => 'activo',
                ]
            );
        }

        $proveedores = [
            [
                'nombre' => 'Distribuidora Escolar Demo',
                'email' => 'escolar@example.com',
                'direccion' => 'Dirección ficticia para demostración.',
            ],
            [
                'nombre' => 'Distribuidora Editorial Demo',
                'email' => 'editorial@example.com',
                'direccion' => 'Dirección ficticia para demostración.',
            ],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::firstOrCreate(
                ['nombre' => $proveedor['nombre']],
                [
                    'email' => $proveedor['email'],
                    'direccion' => $proveedor['direccion'],
                    'estado' => 'activo',
                ]
            );
        }
    }
}