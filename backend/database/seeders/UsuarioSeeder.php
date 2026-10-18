<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $cuentas = [
            [
                'usuario' => 'admin.libreria',
                'nombre' => 'Administrador de demostración',
                'rol' => 'Administrador',
            ],
            [
                'usuario' => 'empleado.libreria',
                'nombre' => 'Empleado de demostración',
                'rol' => 'Empleado',
            ],
        ];

        foreach ($cuentas as $cuenta) {
            if (User::where('usuario', $cuenta['usuario'])->exists()) {
                continue;
            }

            $clave = $this->command?->secret(
                'Contraseña para '.$cuenta['usuario'].' (mínimo 12 caracteres)'
            );

            if (!is_string($clave) || strlen($clave) < 12) {
                throw new RuntimeException(
                    'Debes introducir una contraseña de al menos 12 caracteres.'
                );
            }

            $rol = Rol::where('nombre', $cuenta['rol'])
                ->firstOrFail();

            User::create([
                'nombre' => $cuenta['nombre'],
                'usuario' => $cuenta['usuario'],
                'contrasena_hash' => $clave,
                'id_rol' => $rol->id_rol,
                'estado' => 'activo',
            ]);
        }
    }
}