<?php

namespace App\Http\Requests;

use App\Services\RolService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRolRequest extends FormRequest
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
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'nombre')->ignore($this->route('rol')),
            ],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['integer', Rule::in(app(RolService::class)->availablePermissions()->modelKeys())],
        ];
    }
}
