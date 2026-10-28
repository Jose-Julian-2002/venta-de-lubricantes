<?php

namespace App\Http\Requests;

use App\Services\SucursalContext;
use App\Services\VentaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('ventas.ver');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sucursal_id' => $this->input('sucursal_id') ?: (string) app(SucursalContext::class)->id(),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(VentaService $service): array
    {
        $allowed = $service->availableSucursales()->modelKeys();
        if ($this->user()->can('sucursales.ver')) {
            $allowed[] = 'todas';
        }

        return ['sucursal_id' => ['required', Rule::in($allowed)]];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'sucursal_id.required' => 'Selecciona una sucursal.',
            'sucursal_id.in' => 'Selecciona una sucursal válida para consultar las ventas.',
        ];
    }
}
