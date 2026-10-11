<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $money = ['numeric', 'min:0', 'max:99999999.99', 'regex:/^\d+(\.\d{1,2})?$/'];

        return [
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'fecha_compra' => ['required', 'date_format:Y-m-d\TH:i', 'before_or_equal:now'],
            'detalles' => ['required', 'array', 'min:1', 'max:100'],
            'detalles.*' => ['required', 'array:producto_id,cantidad_cajas,unidades_por_caja,precio_compra_caja,nuevo_precio_venta_menor,nuevo_precio_venta_mayor'],
            'detalles.*.producto_id' => ['required', 'integer', 'distinct', Rule::exists('productos', 'id')->where('estado', true)],
            'detalles.*.cantidad_cajas' => ['required', 'integer', 'min:1', 'max:1000000'],
            'detalles.*.unidades_por_caja' => ['required', 'integer', 'min:1', 'max:1000000'],
            'detalles.*.precio_compra_caja' => ['required', ...$money],
            'detalles.*.nuevo_precio_venta_menor' => ['nullable', ...$money],
            'detalles.*.nuevo_precio_venta_mayor' => ['nullable', ...$money],
            'total' => ['prohibited'], 'usuario_id' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.', 'exists' => 'La selección de :attribute no es válida o está inactiva.',
            'integer' => 'El campo :attribute debe ser un número entero.', 'numeric' => 'El campo :attribute debe ser un número.',
            'min' => 'El campo :attribute debe ser al menos :min.', 'max' => 'El campo :attribute supera el máximo permitido (:max).',
            'regex' => 'El precio debe ser un importe positivo con hasta dos decimales.', 'distinct' => 'Cada producto debe aparecer una sola vez.',
            'array' => 'El detalle contiene campos no permitidos.', 'prohibited' => 'El campo :attribute se calcula automáticamente.',
            'date_format' => 'La fecha debe tener un formato válido.', 'before_or_equal' => 'La fecha de compra no puede estar en el futuro.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['proveedor_id' => 'proveedor', 'fecha_compra' => 'fecha de compra', 'detalles' => 'productos', 'detalles.*.producto_id' => 'producto', 'detalles.*.cantidad_cajas' => 'cantidad de cajas', 'detalles.*.unidades_por_caja' => 'unidades por caja', 'detalles.*.precio_compra_caja' => 'precio por caja'];
    }
}
