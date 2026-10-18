<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'usuario' => fake()->unique()->bothify('prueba_########'),
            'contrasena_hash' => Str::random(32),
            'id_rol' => function (): int {
                return Rol::firstOrCreate([
                    'nombre' => 'Empleado',
                ])->id_rol;
            },
            'estado' => 'activo',
        ];
    }
}