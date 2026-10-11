<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\CompraDetalle;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaService
{
    private const MAX_CENTS = 9999999999;

    public function paginate(): LengthAwarePaginator
    {
        return Venta::query()->with(['cliente', 'usuario'])->withCount('detalles')->orderByDesc('created_at')->orderByDesc('id')->paginate(15);
    }

    public function findForViewing(Venta $venta): Venta
    {
        return $venta->load(['cliente', 'usuario', 'detalles.producto.marca']);
    }

    /** @return array{productos: Collection<int, Producto>, clientes: Collection<int, Cliente>, defaultClienteId: int|null} */
    public function posOptions(): array
    {
        return [
            'productos' => Producto::query()->where('estado', true)->with(['marca', 'categoria', 'ultimaCompraDetalle'])->orderBy('nombre')->get(),
            'clientes' => Cliente::query()->orderBy('nombre')->get(),
            'defaultClienteId' => Cliente::query()->where('nombre', 'Cliente General')->value('id'),
        ];
    }

    /** @param array{token: string, cliente_id: int|string, tipo_pago: string, detalles: array<int, array{producto_id: int|string, cantidad: int|string, tipo_precio: string}>} $data */
    public function create(array $data, Usuario $usuario): Venta
    {
        return DB::transaction(function () use ($data, $usuario): Venta {
            Usuario::query()->whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
            $existing = Venta::query()->where('token', $data['token'])->first();
            if ($existing !== null) {
                if ($existing->usuario_id !== $usuario->getKey()) {
                    throw ValidationException::withMessages(['token' => 'Esta operación pertenece a otro vendedor.']);
                }
                $original = $existing->detalles()->get()->map(fn ($detalle): array => ['producto_id' => (int) $detalle->producto_id, 'cantidad' => (int) $detalle->cantidad, 'tipo_precio' => $detalle->tipo_precio])->sortBy('producto_id')->values()->all();
                $requested = collect($data['detalles'])->map(fn (array $item): array => ['producto_id' => (int) $item['producto_id'], 'cantidad' => (int) $item['cantidad'], 'tipo_precio' => $item['tipo_precio']])->sortBy('producto_id')->values()->all();
                if ((int) $existing->cliente_id !== (int) $data['cliente_id'] || $existing->tipo_pago !== $data['tipo_pago'] || $original !== $requested) {
                    throw ValidationException::withMessages(['token' => 'Esta operación ya se utilizó para otra venta. Inicia una venta nueva.']);
                }

                return $existing;
            }
            $productos = Producto::query()->whereIn('id', array_column($data['detalles'], 'producto_id'))->orderBy('id')->lockForUpdate()->with('marca')->get()->keyBy('id');
            $venta = Venta::query()->create(['usuario_id' => $usuario->getKey(), 'cliente_id' => $data['cliente_id'], 'token' => $data['token'], 'tipo_pago' => $data['tipo_pago'], 'total' => '0.00', 'estado' => 'completada', 'created_at' => now()]);
            $totalCents = 0;
            foreach ($data['detalles'] as $index => $item) {
                $producto = $productos->get($item['producto_id']);
                if ($producto === null || ! $producto->estado) {
                    throw ValidationException::withMessages(["detalles.$index.producto_id" => 'El producto ya no está disponible para venta.']);
                }
                $cantidad = (int) $item['cantidad'];
                if (($item['presentacion'] ?? 'unidad') === 'caja') {
                    $unidadesPorCaja = CompraDetalle::query()->where('producto_id', $producto->getKey())->orderByDesc('id')->lockForUpdate()->value('unidades_por_caja');
                    if ($unidadesPorCaja === null || $unidadesPorCaja < 1 || $cantidad % $unidadesPorCaja !== 0 || $item['tipo_precio'] !== 'mayor') {
                        throw ValidationException::withMessages(["detalles.$index.cantidad" => "Revisa las unidades por caja de {$producto->nombre}. La cantidad debe corresponder a cajas completas con precio mayorista."]);
                    }
                }
                if ($cantidad < 1 || $cantidad > $producto->stock_actual) {
                    throw ValidationException::withMessages(["detalles.$index.cantidad" => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock_actual} unidades."]);
                }
                if (! in_array($item['tipo_precio'], ['menor', 'mayor'], true)) {
                    throw ValidationException::withMessages(["detalles.$index.tipo_precio" => 'El tipo de precio no es válido.']);
                }
                $precio = $producto->{'precio_venta_'.$item['tipo_precio']};
                if ($precio === null) {
                    throw ValidationException::withMessages(["detalles.$index.tipo_precio" => "El producto {$producto->nombre} no tiene precio de venta {$item['tipo_precio']}."]);
                }
                $parts = explode('.', $precio);
                $precioCents = ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');
                if ($precioCents > 0 && $cantidad > intdiv(self::MAX_CENTS - $totalCents, $precioCents)) {
                    throw ValidationException::withMessages(['detalles' => 'El total de la venta supera el máximo permitido (99.999.999,99).']);
                }
                $subtotalCents = $precioCents * $cantidad;
                $totalCents += $subtotalCents;
                $stockAnterior = $producto->stock_actual;
                $detalle = $venta->detalles()->create(['producto_id' => $producto->getKey(), 'nombre_producto' => $producto->nombre, 'marca_producto' => $producto->marca->nombre, 'cantidad' => $cantidad, 'tipo_precio' => $item['tipo_precio'], 'precio_unitario' => $precio, 'subtotal' => $this->money($subtotalCents)]);
                $producto->stock_actual = $stockAnterior - $cantidad;
                $producto->save();
                StockMovimiento::query()->create(['producto_id' => $producto->getKey(), 'usuario_id' => $usuario->getKey(), 'venta_detalle_id' => $detalle->getKey(), 'tipo' => 'salida', 'cantidad' => $cantidad, 'stock_anterior' => $stockAnterior, 'stock_nuevo' => $producto->stock_actual, 'motivo' => 'venta', 'created_at' => now()]);
            }
            $venta->update(['total' => $this->money($totalCents)]);

            return $venta;
        }, 3);
    }

    private function money(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
