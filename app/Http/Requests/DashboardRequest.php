<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mes' => ['nullable', 'date_format:Y-m'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['mes.date_format' => 'Selecciona un mes válido con el formato año-mes.'];
    }
}
