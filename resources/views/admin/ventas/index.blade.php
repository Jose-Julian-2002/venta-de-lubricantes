@extends('layouts.admin')
@section('title', 'Historial de ventas')
@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-semibold">Historial de ventas</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Consulta las ventas realizadas y vuelve a imprimir sus tickets.</p></div>
        @can('ventas.crear')<a href="{{ route('admin.ventas.create') }}" class="rounded-lg bg-blue-700 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Abrir punto de venta</a>@endcan
    </div>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto"><table class="w-full min-w-[42rem] text-left text-sm">
            <thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><tr>
                <th scope="col" class="px-4 py-3">Venta</th><th scope="col" class="px-4 py-3">Fecha</th><th scope="col" class="px-4 py-3">Cliente</th><th scope="col" class="px-4 py-3">Vendedor</th><th scope="col" class="px-4 py-3">Pago</th><th scope="col" class="px-4 py-3 text-right">Total (Bs)</th><th scope="col" class="px-4 py-3">Ticket</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($ventas as $venta)
                <tr><td class="px-4 py-4 font-medium">#{{ $venta->id }}</td><td class="px-4 py-4">{{ $venta->created_at->format('d/m/Y H:i') }}</td><td class="px-4 py-4">{{ $venta->cliente->nombre }}</td><td class="px-4 py-4">{{ $venta->usuario->nombre }}</td><td class="px-4 py-4">{{ ['efectivo' => 'Efectivo', 'qr' => 'QR', 'transferencia' => 'Transferencia'][$venta->tipo_pago] ?? $venta->tipo_pago }}</td><td class="px-4 py-4 text-right tabular-nums">{{ number_format((float) $venta->total, 2, ',', '.') }}</td><td class="px-4 py-4"><a href="{{ route('admin.ventas.show', $venta) }}" aria-label="Ver ticket de venta {{ $venta->id }}" class="py-2 text-blue-700 dark:text-blue-300">Ver ticket</a></td></tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">Todavía no hay ventas registradas.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if($ventas->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $ventas->links() }}</div>@endif
    </div>
</div>
@endsection
