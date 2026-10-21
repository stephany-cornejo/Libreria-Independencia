<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nombre obligatorio y único (el inventario lo referencia por
     * nombre en los selectores); descripción opcional y estado
     * restringido a activo o inactivo.
     */
    public function rules(): array
    {
        $categoria = $this->route('categoria');

        return [
            'nombre' => [
                'required',
                'string',
                'max:50',
                // La tabla usa id_categoria (no 'id'): hay que indicarlo.
                Rule::unique('categorias', 'nombre')
                    ->ignore($categoria?->id_categoria, 'id_categoria'),
            ],
            'descripcion' => ['nullable', 'string', 'max:150'],
            'estado' => ['required', 'string', Rule::in(['activo', 'inactivo'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto.',
            'nombre.max' => 'El nombre no puede superar los 50 caracteres.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',

            'descripcion.string' => 'La descripción debe ser un texto.',
            'descripcion.max' => 'La descripción no puede superar los 150 caracteres.',

            'estado.required' => 'Debes seleccionar el estado.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
            'estado' => 'estado',
        ];
    }
}
