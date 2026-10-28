<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\SucursalProducto;
use Illuminate\Database\Eloquent\Builder;

class InventarioSucursalService
{
    public function __construct(private SucursalContext $context) {}

    /** @return Builder<Producto> */
    public function productos(?int $sucursalId = null): Builder
    {
        $sucursalId ??= $this->context->id();

        return Producto::query()
            ->leftJoin('sucursal_productos as inventario', function ($join) use ($sucursalId): void {
                $join->on('inventario.producto_id', '=', 'productos.id')->where('inventario.sucursal_id', $sucursalId);
            })
            ->select('productos.*')
            ->selectRaw('COALESCE(inventario.stock_actual, 0) as stock_actual');
    }

    /** The caller holds the product lock inside its transaction before creating this row. */
    public function lock(Producto $producto, int $sucursalId): SucursalProducto
    {
        SucursalProducto::query()->firstOrCreate([
            'sucursal_id' => $sucursalId, 'producto_id' => $producto->getKey(),
        ], ['stock_actual' => 0]);

        return SucursalProducto::query()
            ->where('sucursal_id', $sucursalId)->where('producto_id', $producto->getKey())
            ->lockForUpdate()->firstOrFail();
    }
}
