<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'marca_id' => ['required', 'integer', 'exists:marcas,id'],
            'precio_compra_referencia' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'precio_venta_menor' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'precio_venta_mayor' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'estado' => ['required', 'boolean'],
            'stock_actual' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser un texto.',
            'nombre.max' => 'El nombre es demasiado largo.',
            'nombre.unique' => 'Este nombre ya está registrado.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'exists' => 'La selección de :attribute no es válida.',
            'numeric' => 'El campo :attribute debe ser un número.',
            'min' => 'El campo :attribute no puede ser negativo.',
            'max' => 'El campo :attribute supera el valor máximo permitido.',
            'decimal' => 'El campo :attribute admite hasta dos decimales.',
            'boolean' => 'El estado seleccionado no es válido.',
            'prohibited' => 'El stock se modifica mediante movimientos de inventario.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['categoria_id' => 'categoría', 'marca_id' => 'marca', 'precio_compra_referencia' => 'precio de compra de referencia', 'precio_venta_menor' => 'precio de venta menor', 'precio_venta_mayor' => 'precio de venta mayor', 'stock_actual' => 'stock'];
    }
}
