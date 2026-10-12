<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreClienteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('nombre'))) {
            $this->merge(['nombre' => trim($this->input('nombre'))]);
        }
    }

    /** @return array<\Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('nombre')) {
                return;
            }
            if (mb_strtolower($this->input('nombre')) !== 'cliente general') {
                return;
            }
            $cliente = $this->route('cliente');
            if ($cliente instanceof Cliente && $cliente->nombre === 'Cliente General' && $this->input('nombre') === 'Cliente General') {
                return;
            }
            $validator->errors()->add('nombre', 'Cliente General es un nombre reservado para el cliente predeterminado.');
        }];
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
            'nombre' => ['required', 'string', 'max:150'],
            'nit_ci' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:30'],
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
        return ['nombre' => 'nombre del cliente', 'nit_ci' => 'NIT / CI', 'telefono' => 'teléfono'];
    }
}
