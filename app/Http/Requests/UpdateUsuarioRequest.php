<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
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
            'rol_id' => ['required', 'integer', 'exists:roles,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('usuarios', 'email')->ignore($this->route('usuario')),
            ],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
            'estado' => ['required', 'boolean'],
        ];
    }
}
