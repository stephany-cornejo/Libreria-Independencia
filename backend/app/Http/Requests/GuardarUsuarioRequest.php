<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nombre, usuario único, rol y estado son obligatorios. La
     * contraseña se exige solo al crear la cuenta (mínimo 12
     * caracteres, igual que en los seeders); al editar, si se deja
     * en blanco se conserva la contraseña actual.
     */
    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $esCreacion = $usuario === null;

        $reglas = [
            'nombre' => ['required', 'string', 'max:100'],
            'usuario' => [
                'required',
                'string',
                'max:50',
                // La tabla usa id_usuario (no 'id'): hay que indicarlo.
                Rule::unique('usuarios', 'usuario')
                    ->ignore($usuario?->id_usuario, 'id_usuario'),
            ],
            'id_rol' => [
                'required',
                'integer',
                Rule::exists('roles', 'id_rol'),
            ],
            'estado' => [
                'required',
                'string',
                Rule::in(['activo', 'inactivo']),
            ],
            'contrasena' => [
                $esCreacion ? 'required' : 'nullable',
                'string',
                'min:12',
                'max:255',
            ],
        ];

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',

            'usuario.required' => 'El usuario es obligatorio.',
            'usuario.string' => 'El usuario debe ser un texto.',
            'usuario.max' => 'El usuario no puede superar los 50 caracteres.',
            'usuario.unique' => 'Ya existe una cuenta con ese usuario.',

            'id_rol.required' => 'Debes seleccionar un rol.',
            'id_rol.integer' => 'El rol seleccionado no es válido.',
            'id_rol.exists' => 'El rol seleccionado no existe.',

            'estado.required' => 'Debes seleccionar el estado.',
            'estado.in' => 'El estado seleccionado no es válido.',

            'contrasena.required' => 'La contraseña es obligatoria al crear la cuenta.',
            'contrasena.string' => 'La contraseña debe ser un texto.',
            'contrasena.min' => 'La contraseña debe tener al menos 12 caracteres.',
            'contrasena.max' => 'La contraseña no puede superar los 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'usuario' => 'usuario',
            'id_rol' => 'rol',
            'estado' => 'estado',
            'contrasena' => 'contraseña',
        ];
    }
}
