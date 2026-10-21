<?php

namespace App\Http\Requests;

use App\Rules\Isbn;
use Illuminate\Foundation\Http\FormRequest;

class GuardarLibroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas del formulario de libro. El precio y la cantidad son
     * numéricos y positivos; el ISBN, si se informa, debe ser válido.
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'autor' => ['nullable', 'string', 'max:100'],
            'editorial' => ['nullable', 'string', 'max:100'],
            'isbn' => ['nullable', 'string', 'max:20', new Isbn],
            'id_categoria' => ['required', 'integer', 'exists:categorias,id_categoria'],
            'precio' => ['required', 'numeric', 'min:0.01'],
            'cantidad' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El título es obligatorio.',
            'nombre.string' => 'El título debe ser un texto.',
            'nombre.max' => 'El título no puede superar los 100 caracteres.',

            'autor.string' => 'El autor debe ser un texto.',
            'autor.max' => 'El autor no puede superar los 100 caracteres.',

            'editorial.string' => 'La editorial debe ser un texto.',
            'editorial.max' => 'La editorial no puede superar los 100 caracteres.',

            'isbn.string' => 'El ISBN debe ser un texto.',
            'isbn.max' => 'El ISBN no puede superar los 20 caracteres.',

            'id_categoria.required' => 'Debes seleccionar una categoría.',
            'id_categoria.integer' => 'La categoría seleccionada no es válida.',
            'id_categoria.exists' => 'La categoría seleccionada no existe. Recarga la página e inténtalo de nuevo.',

            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un valor numérico.',
            'precio.min' => 'El precio debe ser un número positivo.',

            'cantidad.required' => 'La cantidad en stock es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad no puede ser negativa.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'título',
            'autor' => 'autor',
            'editorial' => 'editorial',
            'isbn' => 'ISBN',
            'id_categoria' => 'categoría',
            'precio' => 'precio',
            'cantidad' => 'cantidad en stock',
        ];
    }
}
