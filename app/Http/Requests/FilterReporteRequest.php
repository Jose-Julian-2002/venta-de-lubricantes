<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FilterReporteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $today = now('America/La_Paz');
        $this->merge([
            'tipo' => $this->input('tipo') ?: 'ventas',
            'desde' => $this->input('desde') ?: $today->copy()->startOfMonth()->format('Y-m-d'),
            'hasta' => $this->input('hasta') ?: $today->format('Y-m-d'),
        ]);
    }

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
            'tipo' => ['required', 'in:ventas,compras,stock'],
            'desde' => ['required', 'date_format:Y-m-d'],
            'hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tipo.in' => 'Selecciona un tipo de reporte válido.',
            'date_format' => 'El campo :attribute debe ser una fecha válida.',
            'required' => 'El campo :attribute es obligatorio.',
            'hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ];
    }
}
