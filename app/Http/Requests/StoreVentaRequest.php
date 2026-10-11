<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVentaRequest extends FormRequest
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
            'token' => ['required', 'uuid'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'tipo_pago' => ['required', 'in:efectivo,qr,transferencia'],
            'detalles' => ['required', 'array', 'min:1', 'max:100'],
            'detalles.*' => ['required', 'array:producto_id,cantidad,tipo_precio,presentacion'],
            'detalles.*.producto_id' => ['required', 'integer', 'distinct', Rule::exists('productos', 'id')->where('estado', true)],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'detalles.*.tipo_precio' => ['required', 'in:menor,mayor'],
            'detalles.*.presentacion' => ['sometimes', 'required', 'in:unidad,caja'],
            'total' => ['prohibited'],
            'usuario_id' => ['prohibited'],
            'estado' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'uuid' => 'El identificador de la venta no es válido. Inicia una venta nueva.',
            'exists' => 'La selección de :attribute no es válida o está inactiva.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'min' => 'El campo :attribute debe ser al menos :min.',
            'max' => 'El campo :attribute supera el máximo permitido (:max).',
            'distinct' => 'Cada producto debe aparecer una sola vez.',
            'array' => 'El carrito contiene campos no permitidos.',
            'in' => 'La selección de :attribute no es válida.',
            'prohibited' => 'El campo :attribute se calcula automáticamente.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['cliente_id' => 'cliente', 'tipo_pago' => 'forma de pago', 'detalles' => 'carrito', 'detalles.*.producto_id' => 'producto', 'detalles.*.cantidad' => 'cantidad', 'detalles.*.tipo_precio' => 'tipo de precio'];
    }
}
