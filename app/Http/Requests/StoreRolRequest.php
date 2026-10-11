<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:50', 'unique:roles,nombre'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['integer', 'exists:permisos,id'],
        ];
    }
}
