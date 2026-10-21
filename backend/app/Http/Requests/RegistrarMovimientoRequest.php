<?php

namespace App\Http\Requests;

use App\Services\Inventario\MovimientoService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * El tipo debe ser entrada o salida; el motivo, uno de los
     * permitidos para el tipo elegido; la cantidad, entera mayor
     * que cero. El stock suficiente se comprueba en el servicio
     * dentro de la transacción (no aquí).
     */
    public function rules(): array
    {
        $tipo = (string) $this->input('tipo');

        return [
            'tipo' => ['required', 'string', Rule::in(['entrada', 'salida'])],
            'motivo' => [
                'required',
                'string',
                Rule::in(MovimientoService::motivosPermitidos($tipo)),
            ],
            'id_producto' => [
                'required',
                'integer',
                Rule::exists('productos', 'id_producto')->where('estado', 'activo'),
            ],
            'cantidad' => ['required', 'integer', 'min:1'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'Debes indicar si es una entrada o una salida.',
            'tipo.in' => 'El tipo de movimiento no es válido.',

            'motivo.required' => 'Debes seleccionar el motivo del movimiento.',
            'motivo.in' => 'El motivo seleccionado no corresponde al tipo de movimiento.',

            'id_producto.required' => 'Debes seleccionar un libro.',
            'id_producto.integer' => 'El libro seleccionado no es válido.',
            'id_producto.exists' => 'El libro seleccionado no existe o está inactivo.',

            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1 unidad.',

            'observaciones.string' => 'Las observaciones deben ser un texto.',
            'observaciones.max' => 'Las observaciones no pueden superar los 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo' => 'tipo de movimiento',
            'motivo' => 'motivo',
            'id_producto' => 'libro',
            'cantidad' => 'cantidad',
            'observaciones' => 'observaciones',
        ];
    }
}
