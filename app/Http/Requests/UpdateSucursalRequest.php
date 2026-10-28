<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSucursalRequest extends StoreSucursalRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sucursales.editar');
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'nombre' => ['required', 'string', 'max:100', Rule::unique('sucursales', 'nombre')->ignore($this->route('sucursal'))],
        ];
    }
}
