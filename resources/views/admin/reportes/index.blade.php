@extends('layouts.admin')
@section('title', 'Reportes')
@section('content')
@php
    $tipo = $filters['tipo'] ?? 'ventas';
    $reporteLabel = ['ventas' => 'Ventas', 'compras' => 'Compras', 'stock' => 'Existencias actuales'][$tipo];
    $metricas = match ($tipo) {
        'compras' => ['Compras registradas' => $resumen['comprasCantidad'], 'Total comprado (Bs)' => number_format((float) $resumen['comprasTotal'], 2, ',', '.')],
        'stock' => ['Productos' => $resumen['productosCantidad'], 'Unidades en stock' => $resumen['unidadesStock'], 'Productos agotados' => $resumen['agotados']],
        default => ['Ventas registradas' => $resumen['ventasCantidad'], 'Unidades vendidas' => $resumen['unidadesVendidas'], 'Total vendido (Bs)' => number_format((float) $resumen['ventasTotal'], 2, ',', '.'), 'Ganancia bruta estimada (Bs)' => $resumen['gananciaTotal'] === null ? 'Pendiente' : number_format((float) $resumen['gananciaTotal'], 2, ',', '.')],
    };
@endphp
<style>
    @media print {
        aside, header, nav, .report-print-hide, [role="status"], [role="alert"] { display: none !important; }
        body, main, .report-surface, .report-surface * { background: white !important; color: black !important; }
        main { padding: 0 !important; }
        .report-surface { max-width: none !important; }
        .report-table-wrapper { overflow: visible !important; border: 0 !important; }
        .report-surface table { min-width: 0 !important; width: 100% !important; font-size: 10pt; }
        .report-surface th, .report-surface td { padding: 8px !important; border-bottom: 1px solid #ddd; }
        .report-surface tr { break-inside: avoid; }
        .report-surface thead { display: table-header-group; }
    }
</style>
<div class="report-surface mx-auto max-w-6xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-semibold">Reportes</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Consulta las ventas, las compras y las existencias del negocio.</p></div>
        <button type="button" onclick="window.print()" class="report-print-hide min-h-11 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium hover:bg-slate-100 focus-visible:outline-blue-600 dark:border-slate-700 dark:hover:bg-slate-800">Imprimir página</button>
    </div>
    <form action="{{ route('admin.reportes.index') }}" method="GET" x-data="{tipo: {{ Illuminate\Support\Js::from($tipo) }}}" class="report-print-hide mb-6 grid items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div><label for="tipo" class="mb-2 block text-sm font-medium">Reporte</label><select id="tipo" name="tipo" x-model="tipo" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900">@foreach (['ventas' => 'Ventas', 'compras' => 'Compras', 'stock' => 'Existencias actuales'] as $value => $label)<option value="{{ $value }}" @selected($tipo === $value)>{{ $label }}</option>@endforeach</select></div>
        @foreach (['desde' => 'Desde', 'hasta' => 'Hasta'] as $campo => $label)
            <div><label for="{{ $campo }}" class="mb-2 block text-sm font-medium">{{ $label }}</label><input type="date" id="{{ $campo }}" name="{{ $campo }}" value="{{ $filters[$campo] ?? '' }}" x-bind:disabled="tipo === 'stock'" @disabled($tipo === 'stock') class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900" @error($campo) aria-invalid="true" aria-describedby="{{ $campo }}-error" @enderror>@error($campo)<p id="{{ $campo }}-error" role="alert" class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror</div>
        @endforeach
        <div class="flex gap-3"><button type="submit" class="min-h-11 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Consultar</button><a href="{{ route('admin.reportes.index') }}" class="flex min-h-11 items-center px-2 text-sm text-blue-700 dark:text-blue-300">Limpiar</a></div>
        <p x-show="tipo === 'stock'" class="text-sm text-slate-600 sm:col-span-2 lg:col-span-4 dark:text-slate-400">Las existencias muestran el stock actual. Las fechas no se aplican a este reporte.</p>
    </form>
    <h2 class="text-lg font-semibold">{{ $reporteLabel }}</h2>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">@if($tipo === 'stock')Stock actual al consultar el reporte.@else Período: {{ !empty($filters['desde']) ? Illuminate\Support\Carbon::parse($filters['desde'])->format('d/m/Y') : 'Desde el inicio' }} — {{ !empty($filters['hasta']) ? Illuminate\Support\Carbon::parse($filters['hasta'])->format('d/m/Y') : 'Sin fecha final' }}. Fechas en hora de Bolivia.@endif</p>
    <dl class="my-5 grid gap-x-6 gap-y-4 border-y border-slate-200 py-5 sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-800">
        @foreach ($metricas as $label => $value)<div><dt class="text-sm text-slate-600 dark:text-slate-400">{{ $label }}</dt><dd class="mt-1 text-lg font-semibold tabular-nums">{{ $value }}</dd></div>@endforeach
    </dl>
    @if($tipo === 'ventas')<p class="mb-4 max-w-3xl text-sm text-slate-600 dark:text-slate-400">La ganancia bruta estimada usa el costo de la última compra anterior a cada venta. No incluye gastos operativos ni impuestos.</p>@endif
    @if($tipo === 'ventas' && ($resumen['costosPendientes'] ?? 0) > 0)<p class="mb-4 text-sm text-amber-800 dark:text-amber-300">Hay {{ $resumen['costosPendientes'] }} unidades vendidas sin costo de compra anterior registrado. La ganancia completa queda pendiente.</p>@endif
    <div class="mb-3 flex flex-wrap justify-between gap-2 text-sm text-slate-600 dark:text-slate-400"><p>El resumen corresponde a todos los registros del filtro.</p><p>Página {{ $registros->currentPage() }} de {{ $registros->lastPage() }} · {{ $registros->total() }} registros</p></div>
    <div class="report-table-wrapper overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="report-table-wrapper overflow-x-auto"><table class="w-full min-w-[44rem] text-left text-sm">
            <thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><tr>
                @foreach (match ($tipo) {'compras' => ['Compra', 'Fecha', 'Proveedor', 'Registrada por', 'Total (Bs)'], 'stock' => ['Producto', 'Marca', 'Categoría', 'Stock (unidades)', 'Estado'], default => ['Venta', 'Fecha', 'Cliente', 'Vendedor', 'Pago', 'Total (Bs)']} as $label)<th scope="col" class="px-4 py-3">{{ $label }}</th>@endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($registros as $registro)
                    <tr>
                        @if($tipo === 'stock')
                            <td class="px-4 py-4 font-medium">{{ $registro->nombre }}</td><td class="px-4 py-4">{{ $registro->marca->nombre }}</td><td class="px-4 py-4">{{ $registro->categoria->nombre }}</td><td class="px-4 py-4 tabular-nums">{{ $registro->stock_actual }}</td><td class="px-4 py-4">{{ $registro->estado ? 'Activo' : 'Inactivo' }}</td>
                        @elseif($tipo === 'compras')
                            <td class="px-4 py-4 font-medium">#{{ $registro->id }}</td><td class="px-4 py-4">{{ $registro->fecha_compra->format('d/m/Y') }}</td><td class="px-4 py-4">{{ $registro->proveedor->nombre_empresa }}</td><td class="px-4 py-4">{{ $registro->usuario->nombre }}</td><td class="px-4 py-4 tabular-nums">{{ number_format((float) $registro->total, 2, ',', '.') }}</td>
                        @else
                            <td class="px-4 py-4 font-medium">#{{ $registro->id }}</td><td class="px-4 py-4">{{ $registro->created_at->copy()->timezone('America/La_Paz')->format('d/m/Y H:i') }}</td><td class="px-4 py-4">{{ $registro->cliente->nombre }}</td><td class="px-4 py-4">{{ $registro->usuario->nombre }}</td><td class="px-4 py-4">{{ ['efectivo' => 'Efectivo', 'qr' => 'QR', 'transferencia' => 'Transferencia'][$registro->tipo_pago] ?? $registro->tipo_pago }}</td><td class="px-4 py-4 tabular-nums">{{ number_format((float) $registro->total, 2, ',', '.') }}</td>
                        @endif
                    </tr>
                @empty<tr><td colspan="{{ $tipo === 'ventas' ? 6 : 5 }}" class="px-4 py-10 text-center text-slate-600 dark:text-slate-400">No hay registros para este reporte con los filtros seleccionados.</td></tr>@endforelse
            </tbody>
        </table></div>
        @if($registros->hasPages())<div class="report-print-hide border-t border-slate-200 p-4 dark:border-slate-800">{{ $registros->links() }}</div>@endif
    </div>
</div>
@endsection
