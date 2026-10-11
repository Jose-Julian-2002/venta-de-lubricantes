@extends('layouts.admin')
@section('title', 'Historial de compras')
@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-semibold">Historial de compras</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Consulta las compras registradas y el detalle de la mercadería recibida.</p></div>
        @can('compras.crear')<a href="{{ route('admin.compras.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Registrar compra</a>@endcan
    </div>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto"><table class="w-full min-w-[44rem] text-left text-sm">
            <thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><tr>
                <th scope="col" class="px-4 py-3">Compra</th><th scope="col" class="px-4 py-3">Fecha</th><th scope="col" class="px-4 py-3">Proveedor</th><th scope="col" class="px-4 py-3">Registrada por</th><th scope="col" class="px-4 py-3 text-right">Total (Bs)</th><th scope="col" class="px-4 py-3">Acciones</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($compras as $compra)
                <tr><td class="px-4 py-4 font-medium">#{{ $compra->id }}</td><td class="px-4 py-4">{{ $compra->fecha_compra->format('d/m/Y H:i') }}</td><td class="px-4 py-4">{{ $compra->proveedor->nombre_empresa }}</td><td class="px-4 py-4">{{ $compra->usuario->nombre }}</td><td class="px-4 py-4 text-right tabular-nums">{{ number_format((float) $compra->total, 2, ',', '.') }}</td><td class="px-4 py-4"><a href="{{ route('admin.compras.show', $compra) }}" aria-label="Ver detalle de compra {{ $compra->id }}" class="py-2 text-blue-700 dark:text-blue-300">Ver detalle</a></td></tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">Todavía no hay compras registradas.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if ($compras->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $compras->links() }}</div>@endif
    </div>
</div>
@endsection
