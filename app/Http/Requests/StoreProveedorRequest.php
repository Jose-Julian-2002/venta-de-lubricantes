<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre_empresa' => ['required', 'string', 'max:150'],
            'nombre' => ['nullable', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'nit_ci' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser un texto.',
            'max' => 'El campo :attribute no puede superar :max caracteres.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['nombre_empresa' => 'empresa', 'nombre' => 'contacto', 'telefono' => 'teléfono', 'nit_ci' => 'NIT / CI', 'direccion' => 'dirección'];
    }
}
