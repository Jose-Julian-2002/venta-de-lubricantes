<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sucursales.crear');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100', 'unique:sucursales,nombre'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'estado' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Indica el nombre de la sucursal.',
            'nombre.unique' => 'Ya existe una sucursal con este nombre.',
            'nombre.max' => 'El nombre no debe superar los 100 caracteres.',
            'direccion.max' => 'La dirección no debe superar los 255 caracteres.',
            'telefono.max' => 'El teléfono no debe superar los 30 caracteres.',
            'string' => 'El campo :attribute debe ser un texto.',
            'estado.required' => 'Selecciona el estado de la sucursal.',
            'estado.boolean' => 'Selecciona un estado válido.',
        ];
    }
}
