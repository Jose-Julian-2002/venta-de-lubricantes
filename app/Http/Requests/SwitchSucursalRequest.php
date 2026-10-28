<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwitchSucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sucursales.ver');
    }

    public function rules(): array
    {
        return ['sucursal_id' => ['required', 'integer', Rule::exists('sucursales', 'id')->where('estado', true)]];
    }

    public function messages(): array
    {
        return [
            'sucursal_id.required' => 'Selecciona una sucursal.',
            'sucursal_id.integer' => 'Selecciona una sucursal válida.',
            'sucursal_id.exists' => 'La sucursal no existe o está inactiva.',
        ];
    }
}
