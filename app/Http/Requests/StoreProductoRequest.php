<?php

namespace App\Http\Requests;

use App\Models\Marca;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'precio_venta_menor' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'precio_venta_mayor' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'estado' => ['required', 'boolean'],
            'stock_actual' => ['prohibited'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['nombre', 'marca_id'])) {
                    return;
                }

                $marca = Marca::query()->find($this->integer('marca_id'));
                if ($marca === null) {
                    return;
                }

                $pattern = '/(?<![\\p{L}\\p{N}])'.preg_quote($marca->nombre, '/').'(?![\\p{L}\\p{N}])/iu';
                if (preg_match($pattern, $this->string('nombre')->toString()) === 1) {
                    $validator->errors()->add('nombre', 'El nombre no debe incluir la marca seleccionada. Indica la línea, viscosidad o presentación.');
                }
            },
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
        return ['categoria_id' => 'categoría', 'marca_id' => 'marca', 'precio_venta_menor' => 'precio de venta menor', 'precio_venta_mayor' => 'precio de venta mayor', 'stock_actual' => 'stock'];
    }
}
