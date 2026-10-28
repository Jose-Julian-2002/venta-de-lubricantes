<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\StockMovimiento;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompraService
{
    public function __construct(private SucursalContext $context, private InventarioSucursalService $inventario) {}

    private const MAX_CENTS = 9999999999;

    private const MAX_STOCK = 4294967295;

    /** @param array{proveedor_id?: int|string|null, desde?: string|null, hasta?: string|null} $filters */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Compra::query()->where('sucursal_id', $this->context->id())->with(['proveedor', 'usuario', 'sucursal'])->withCount('detalles')
            ->when($filters['proveedor_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('proveedor_id', $id))
            ->when($filters['desde'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('fecha_compra', '>=', $date))
            ->when($filters['hasta'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('fecha_compra', '<=', $date))
            ->orderByDesc('fecha_compra')->orderByDesc('id')->paginate(15)->withQueryString();
    }

    /** @return array{proveedores: Collection<int, Proveedor>, productos: Collection<int, Producto>} */
    public function formOptions(): array
    {
        return ['proveedores' => Proveedor::query()->orderBy('nombre_empresa')->get(), 'productos' => $this->inventario->productos()->where('estado', true)->with(['marca', 'categoria'])->orderBy('nombre')->get()];
    }

    public function findForViewing(Compra $compra): Compra
    {
        $this->context->assertCurrent((int) $compra->sucursal_id);

        return $compra->load(['sucursal', 'proveedor', 'usuario', 'detalles.producto.marca', 'detalles.producto.categoria']);
    }

    /** @param array{proveedor_id: int|string, fecha_compra: string, detalles: array<int, array{producto_id: int|string, cantidad_cajas: int|string, unidades_por_caja: int|string, precio_compra_caja: string|int|float, nuevo_precio_venta_menor?: string|int|float|null, nuevo_precio_venta_mayor?: string|int|float|null}>} $data */
    public function create(array $data, Usuario $usuario): Compra
    {
        $sucursalId = $this->context->forUsuario($usuario);
        $this->context->assertActive($sucursalId);

        return DB::transaction(function () use ($data, $usuario, $sucursalId): Compra {
            $ids = array_column($data['detalles'], 'producto_id');
            $productos = Producto::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $compra = Compra::query()->create(['sucursal_id' => $sucursalId, 'proveedor_id' => $data['proveedor_id'], 'usuario_id' => $usuario->getKey(), 'fecha_compra' => $data['fecha_compra'], 'total' => '0.00']);
            $totalCents = 0;
            foreach ($data['detalles'] as $index => $item) {
                $producto = $productos->get($item['producto_id']);
                if ($producto === null || ! $producto->estado) {
                    throw ValidationException::withMessages(["detalles.$index.producto_id" => 'El producto ya no está disponible para compras.']);
                }
                $cajas = (int) $item['cantidad_cajas'];
                $unidadesPorCaja = (int) $item['unidades_por_caja'];
                $cantidad = $cajas * $unidadesPorCaja;
                $stock = $this->inventario->lock($producto, $sucursalId);
                $stockAnterior = $stock->stock_actual;
                if ($cantidad > self::MAX_STOCK - $stockAnterior || $cantidad > self::MAX_STOCK - $producto->stock_actual) {
                    throw ValidationException::withMessages(["detalles.$index.cantidad_cajas" => 'La cantidad supera la capacidad máxima del inventario.']);
                }
                $precioCents = $this->toCents($item['precio_compra_caja']);
                $subtotalCents = $precioCents * $cajas;
                $totalCents += $subtotalCents;
                if ($totalCents > self::MAX_CENTS) {
                    throw ValidationException::withMessages(['detalles' => 'El total de la compra supera el máximo permitido (99.999.999,99).']);
                }
                $detalle = $compra->detalles()->create([
                    'producto_id' => $producto->getKey(), 'cantidad_cajas' => $cajas, 'unidades_por_caja' => $unidadesPorCaja,
                    'cantidad_total_unidades' => $cantidad, 'precio_compra_caja' => $this->money($precioCents),
                    'costo_unitario' => $this->money(intdiv($precioCents + intdiv($unidadesPorCaja, 2), $unidadesPorCaja)),
                    'subtotal' => $this->money($subtotalCents),
                ]);
                $stock->update(['stock_actual' => $stockAnterior + $cantidad]);
                $producto->stock_actual += $cantidad;
                foreach (['menor', 'mayor'] as $tipo) {
                    $precio = $item['nuevo_precio_venta_'.$tipo] ?? null;
                    if ($precio !== null && $precio !== '') {
                        $producto->{'precio_venta_'.$tipo} = $this->money($this->toCents($precio));
                    }
                }
                $producto->save();
                StockMovimiento::query()->create([
                    'sucursal_id' => $sucursalId, 'producto_id' => $producto->getKey(), 'usuario_id' => $usuario->getKey(), 'compra_detalle_id' => $detalle->getKey(),
                    'tipo' => 'entrada', 'cantidad' => $cantidad, 'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stock->stock_actual, 'motivo' => 'compra', 'created_at' => now(),
                ]);
            }
            $compra->update(['total' => $this->money($totalCents)]);

            return $compra;
        }, 3);
    }

    private function toCents(string|int|float $value): int
    {
        $parts = explode('.', (string) $value);

        return ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');
    }

    private function money(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
