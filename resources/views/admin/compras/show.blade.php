@extends('layouts.admin')
@section('title', 'Detalle de compra #'.$compra->id)
@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4"><h1 class="text-2xl font-semibold">Compra #{{ $compra->id }}</h1><a href="{{ route('admin.compras.index') }}" class="py-2 text-sm text-blue-700 dark:text-blue-300">Volver al historial</a></div>
    <dl class="mb-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-800 dark:bg-slate-900">
        <div><dt class="text-sm text-slate-600 dark:text-slate-400">Proveedor</dt><dd class="mt-1 font-medium">{{ $compra->proveedor->nombre_empresa }}</dd></div>
        <div><dt class="text-sm text-slate-600 dark:text-slate-400">Fecha de compra</dt><dd class="mt-1 font-medium">{{ $compra->fecha_compra->format('d/m/Y H:i') }}</dd></div>
        <div><dt class="text-sm text-slate-600 dark:text-slate-400">Registrada por</dt><dd class="mt-1 font-medium">{{ $compra->usuario->nombre }}</dd></div>
        <div><dt class="text-sm text-slate-600 dark:text-slate-400">Total de compra</dt><dd class="mt-1 text-xl font-semibold tabular-nums">Bs {{ number_format((float) $compra->total, 2, ',', '.') }}</dd></div>
    </dl>
    <h2 class="mb-3 text-lg font-semibold">Mercadería recibida</h2>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"><div class="overflow-x-auto">
        <table class="w-full min-w-[64rem] text-left text-sm">
            <thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><tr>
                <th scope="col" class="px-4 py-3">Producto</th><th scope="col" class="px-4 py-3 text-right">Cajas</th><th scope="col" class="px-4 py-3 text-right">Unidades por caja</th><th scope="col" class="px-4 py-3 text-right">Unidades recibidas</th><th scope="col" class="px-4 py-3 text-right">Compra por caja (Bs)</th><th scope="col" class="px-4 py-3 text-right">Costo por unidad (Bs)</th><th scope="col" class="px-4 py-3 text-right">Subtotal (Bs)</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($compra->detalles as $detalle)
                <tr><td class="px-4 py-4"><span class="block font-medium">{{ $detalle->producto->nombre }}</span><span class="text-slate-600 dark:text-slate-400">{{ $detalle->producto->marca->nombre }}</span>@can('stock.ver')<a href="{{ route('admin.stock.kardex', $detalle->producto) }}" class="mt-1 block text-sm text-blue-700 dark:text-blue-300">Ver Kardex</a>@endcan</td><td class="px-4 py-4 text-right tabular-nums">{{ $detalle->cantidad_cajas }}</td><td class="px-4 py-4 text-right tabular-nums">{{ $detalle->unidades_por_caja }}</td><td class="px-4 py-4 text-right font-medium tabular-nums">{{ $detalle->cantidad_total_unidades }}</td><td class="px-4 py-4 text-right tabular-nums">{{ number_format((float) $detalle->precio_compra_caja, 2, ',', '.') }}</td><td class="px-4 py-4 text-right tabular-nums">{{ number_format((float) $detalle->costo_unitario, 2, ',', '.') }}</td><td class="px-4 py-4 text-right tabular-nums">{{ number_format((float) $detalle->subtotal, 2, ',', '.') }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
    <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">Las unidades recibidas quedaron registradas como entradas en el Kardex de cada producto.</p>
</div>
@endsection
