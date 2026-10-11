@extends('layouts.admin')
@section('title', 'Kardex del producto')
@section('content')
<div class="mx-auto max-w-6xl">
    <a href="{{ route('admin.stock.index') }}" class="text-sm text-blue-700 dark:text-blue-300">Volver a stock</a>
    <h1 class="mt-4 text-2xl font-semibold">{{ $producto->nombre }}</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $producto->marca->nombre }} · {{ $producto->categoria->nombre }} · Stock actual: <strong>{{ $producto->stock_actual }} unidades</strong></p>
    <h2 class="mb-4 mt-7 text-lg font-semibold">Historial de movimientos</h2>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto"><table class="w-full min-w-[55rem] text-left text-sm">
            <thead class="bg-slate-100 dark:bg-slate-800"><tr>@foreach (['Fecha de registro', 'Tipo', 'Motivo', 'Cantidad', 'Stock anterior', 'Stock nuevo', 'Usuario', 'Origen'] as $label)<th scope="col" class="px-4 py-3">{{ $label }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($movimientos as $movimiento)
                    <tr>
                        <td class="px-4 py-4">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-4">{{ $movimiento->tipo === 'entrada' ? 'Entrada' : 'Salida' }}</td>
                        <td class="px-4 py-4">{{ $movimiento->motivo === 'compra' ? 'Compra' : $movimiento->motivo }}</td>
                        <td class="px-4 py-4 tabular-nums">{{ $movimiento->cantidad }}</td>
                        <td class="px-4 py-4 tabular-nums">{{ $movimiento->stock_anterior }}</td>
                        <td class="px-4 py-4 tabular-nums">{{ $movimiento->stock_nuevo }}</td>
                        <td class="px-4 py-4">{{ $movimiento->usuario->nombre }}</td>
                        <td class="px-4 py-4">
                            @if ($movimiento->compraDetalle)
                                @can('compras.ver')
                                    <a href="{{ route('admin.compras.show', $movimiento->compraDetalle->compra_id) }}" class="text-blue-700 dark:text-blue-300">Compra #{{ $movimiento->compraDetalle->compra_id }}</a>
                                @else
                                    Compra #{{ $movimiento->compraDetalle->compra_id }}
                                @endcan
                            @else — @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-slate-500">Este producto aún no tiene movimientos.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if ($movimientos->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $movimientos->links() }}</div>@endif
    </div>
</div>
@endsection
