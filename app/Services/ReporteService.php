<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReporteService
{
    public function __construct(private DashboardService $dashboard) {}

    /** @param array{tipo: string, desde: string, hasta: string} $filters
     * @return array{filters: array{tipo: string, desde: string, hasta: string}, registros: LengthAwarePaginator, resumen: array<string, mixed>}
     */
    public function report(array $filters): array
    {
        if ($filters['tipo'] === 'ventas') {
            $desde = CarbonImmutable::createFromFormat('!Y-m-d', $filters['desde'], 'America/La_Paz')->setTimezone(config('app.timezone'));
            $hasta = CarbonImmutable::createFromFormat('!Y-m-d', $filters['hasta'], 'America/La_Paz')->addDay()->setTimezone(config('app.timezone'));
            $query = Venta::query()->with(['cliente', 'usuario'])->where('estado', 'completada')->where('created_at', '>=', $desde)->where('created_at', '<', $hasta);
            $resumen = $this->dashboard->resumenEntre($filters['desde'], $filters['hasta']);
            $registros = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(20)->withQueryString();
        } elseif ($filters['tipo'] === 'compras') {
            $query = Compra::query()->with(['proveedor', 'usuario'])->whereDate('fecha_compra', '>=', $filters['desde'])->whereDate('fecha_compra', '<=', $filters['hasta']);
            $cents = 0;
            foreach ((clone $query)->select('id', 'total')->cursor() as $compra) {
                $parts = explode('.', $compra->total);
                $cents += ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');
            }
            $resumen = ['comprasTotal' => intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT), 'comprasCantidad' => (clone $query)->count()];
            $registros = $query->orderByDesc('fecha_compra')->orderByDesc('id')->paginate(20)->withQueryString();
        } else {
            $query = Producto::query()->with(['marca', 'categoria']);
            $resumen = ['productosCantidad' => (clone $query)->count(), 'unidadesStock' => (int) (clone $query)->sum('stock_actual'), 'agotados' => (clone $query)->where('stock_actual', 0)->count()];
            $registros = $query->orderBy('nombre')->orderBy('id')->paginate(20)->withQueryString();
        }

        return ['filters' => $filters, 'registros' => $registros, 'resumen' => $resumen];
    }
}
