<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nombre obligatorio; contacto opcional (teléfono, correo y
     * dirección); estado restringido a activo o inactivo.
     */
    public function rules(): array
    {
        $proveedor = $this->route('proveedor');

        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                // La tabla usa id_proveedor (no 'id'): hay que indicarlo.
                Rule::unique('proveedores', 'nombre')
                    ->ignore($proveedor?->id_proveedor, 'id_proveedor'),
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:150'],
            'estado' => ['required', 'string', Rule::in(['activo', 'inactivo'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'nombre.unique' => 'Ya existe un proveedor con ese nombre.',

            'telefono.string' => 'El teléfono debe ser un texto.',
            'telefono.max' => 'El teléfono no puede superar los 20 caracteres.',

            'email.email' => 'El correo debe ser una dirección válida.',
            'email.max' => 'El correo no puede superar los 100 caracteres.',

            'direccion.string' => 'La dirección debe ser un texto.',
            'direccion.max' => 'La dirección no puede superar los 150 caracteres.',

            'estado.required' => 'Debes seleccionar el estado.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'telefono' => 'teléfono',
            'email' => 'correo',
            'direccion' => 'dirección',
            'estado' => 'estado',
        ];
    }
}
