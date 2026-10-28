<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StockService
{
    public function __construct(private ProductoService $productoService, private SucursalContext $context, private InventarioSucursalService $inventario) {}

    /** @param array{buscar?: string|null, categoria_id?: int|string|null, marca_id?: int|string|null} $filters */
    public function inventory(array $filters): LengthAwarePaginator
    {
        return $this->productoService->paginate($filters);
    }

    /** @return array<string, mixed> */
    public function formOptions(): array
    {
        return $this->productoService->formOptions();
    }

    public function findForViewing(Producto $producto): Producto
    {
        return $this->inventario->productos()->where('productos.id', $producto->getKey())->with(['categoria', 'marca'])->firstOrFail();
    }

    public function movements(Producto $producto): LengthAwarePaginator
    {
        return $producto->stockMovimientos()->where('sucursal_id', $this->context->id())->with(['usuario', 'compraDetalle.compra', 'ventaDetalle.venta'])->orderByDesc('id')->paginate(20);
    }
}
