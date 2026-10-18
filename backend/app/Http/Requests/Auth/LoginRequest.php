<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'usuario.required' => 'Debes indicar tu usuario.',
            'usuario.string' => 'El usuario debe ser un texto.',
            'usuario.max' => 'El usuario no puede superar los 50 caracteres.',
            'password.required' => 'Debes indicar tu contraseña.',
            'password.string' => 'La contraseña debe ser un texto.',
        ];
    }
}
