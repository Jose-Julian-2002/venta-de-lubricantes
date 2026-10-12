<?php

namespace App\Services;

use App\Models\CompraDetalle;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\CarbonImmutable;

class DashboardService
{
    /** @return array{mes: string, ventasTotal: string, gananciaTotal: string|null, costosPendientes: int, ventasCantidad: int, unidadesVendidas: int, marcaMasVendida: array{nombre: string, unidades: int}|null, marcasTop: list<array{nombre: string, unidades: int}>, productosTop: list<array{nombre: string, marca: string, unidades: int}>, productoMasVendido: array{nombre: string, marca: string, unidades: int}|null} */
    public function resumen(?string $mes = null): array
    {
        $inicio = CarbonImmutable::createFromFormat('!Y-m', $mes ?? now('America/La_Paz')->format('Y-m'), 'America/La_Paz');

        return ['mes' => $inicio->format('Y-m'), ...$this->resumenEntre($inicio->format('Y-m-d'), $inicio->endOfMonth()->format('Y-m-d'))];
    }

    /** @return array{ventasTotal: string, gananciaTotal: string|null, costosPendientes: int, ventasCantidad: int, unidadesVendidas: int, marcaMasVendida: array{nombre: string, unidades: int}|null, marcasTop: list<array{nombre: string, unidades: int}>, productosTop: list<array{nombre: string, marca: string, unidades: int}>, productoMasVendido: array{nombre: string, marca: string, unidades: int}|null} */
    public function resumenEntre(string $desde, string $hasta): array
    {
        $desde = CarbonImmutable::createFromFormat('!Y-m-d', $desde, 'America/La_Paz')->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
        $hasta = CarbonImmutable::createFromFormat('!Y-m-d', $hasta, 'America/La_Paz')->addDay()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
        $ventas = Venta::query()->where('estado', 'completada')->where('created_at', '>=', $desde)->where('created_at', '<', $hasta);
        $cantidadVentas = (clone $ventas)->count();
        $totalCents = 0;
        foreach ((clone $ventas)->select('id', 'total')->cursor() as $venta) {
            $totalCents += $this->toCents($venta->total);
        }
        $costo = CompraDetalle::query()->select('compra_detalles.costo_unitario')
            ->join('compras', 'compras.id', '=', 'compra_detalles.compra_id')
            ->join('stock_movimientos', 'stock_movimientos.compra_detalle_id', '=', 'compra_detalles.id')
            ->whereColumn('compra_detalles.producto_id', 'venta_detalles.producto_id')
            ->where('stock_movimientos.tipo', 'entrada')->where('stock_movimientos.motivo', 'compra')
            ->whereColumn('stock_movimientos.created_at', '<=', 'ventas.created_at')
            ->whereColumn('compras.fecha_compra', '<=', 'ventas.created_at')
            ->orderByDesc('stock_movimientos.created_at')->orderByDesc('stock_movimientos.id')->limit(1);
        $detalles = VentaDetalle::query()
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->join('marcas', 'marcas.id', '=', 'productos.marca_id')
            ->where('ventas.estado', 'completada')->where('ventas.created_at', '>=', $desde)->where('ventas.created_at', '<', $hasta);
        $gananciaCents = 0;
        $pendientes = 0;
        $unidades = 0;
        foreach ((clone $detalles)->select('venta_detalles.*')->addSelect(['costo_estimado' => $costo])->cursor() as $detalle) {
            $unidades += $detalle->cantidad;
            if ($detalle->costo_estimado === null) {
                $pendientes += $detalle->cantidad;
            } else {
                $gananciaCents += $this->toCents($detalle->subtotal) - ($this->toCents((string) $detalle->costo_estimado) * $detalle->cantidad);
            }
        }
        $marcas = (clone $detalles)->select('marcas.id', 'marcas.nombre')
            ->selectRaw('SUM(venta_detalles.cantidad) as unidades')
            ->groupBy('marcas.id', 'marcas.nombre')->orderByDesc('unidades')->orderBy('marcas.id')->limit(5)->get()->map(fn ($marca): array => ['nombre' => $marca->nombre, 'unidades' => (int) $marca->unidades])->all();
        $productos = (clone $detalles)->select('productos.id', 'marcas.nombre as marca')
            ->selectRaw('MAX(venta_detalles.nombre_producto) as nombre, SUM(venta_detalles.cantidad) as unidades')
            ->groupBy('productos.id', 'marcas.nombre')->orderByDesc('unidades')->orderBy('productos.id')->limit(5)->get()->map(fn ($producto): array => ['nombre' => $producto->nombre, 'marca' => $producto->marca, 'unidades' => (int) $producto->unidades])->all();

        return [
            'ventasTotal' => $this->money($totalCents),
            'gananciaTotal' => $pendientes > 0 ? null : $this->money($gananciaCents),
            'costosPendientes' => $pendientes,
            'ventasCantidad' => $cantidadVentas,
            'unidadesVendidas' => $unidades,
            'marcasTop' => $marcas,
            'marcaMasVendida' => $marcas[0] ?? null,
            'productosTop' => $productos,
            'productoMasVendido' => $productos[0] ?? null,
        ];
    }

    private function toCents(string $value): int
    {
        $parts = explode('.', $value);

        return ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');
    }

    private function money(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $cents = abs($cents);

        return $sign.intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
