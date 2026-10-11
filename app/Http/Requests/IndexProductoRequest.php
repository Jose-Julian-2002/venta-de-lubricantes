<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['buscar' => ['nullable', 'string', 'max:150'], 'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'], 'marca_id' => ['nullable', 'integer', 'exists:marcas,id']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['buscar.string' => 'La búsqueda debe ser un texto.', 'buscar.max' => 'La búsqueda admite hasta 150 caracteres.', 'integer' => 'El filtro debe ser un identificador válido.', 'exists' => 'La opción seleccionada no existe.'];
    }
}
