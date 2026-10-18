<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local')) {
            throw new RuntimeException(
                'Esta carga de demostración solo se ejecuta en entorno local.'
            );
        }

        DB::transaction(function (): void {
            $this->call([
                RolSeeder::class,
                UsuarioSeeder::class,
                CatalogoSeeder::class,
                InventarioDemoSeeder::class,
            ]);
        });
    }
}