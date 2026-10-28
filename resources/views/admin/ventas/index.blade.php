@extends('layouts.admin')
@section('title', 'Historial de ventas')
@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-semibold">Historial de ventas</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Consulta las ventas realizadas y vuelve a imprimir sus tickets.</p></div>
        @can('ventas.crear')<a href="{{ route('admin.ventas.create') }}" class="rounded-lg bg-blue-700 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Abrir punto de venta</a>@endcan
    </div>
    @can('sucursales.ver')
        <form action="{{ route('admin.ventas.index') }}" method="GET" class="mb-5 flex flex-wrap items-end gap-3">
            <div class="w-full sm:w-72">
                <label for="filtro-sucursal" class="mb-2 block text-sm font-medium">Filtrar ventas por sucursal</label>
                <select id="filtro-sucursal" name="sucursal_id" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus-visible:outline-blue-600 dark:border-slate-700 dark:bg-slate-900" @error('sucursal_id') aria-invalid="true" aria-describedby="sucursal-error" @enderror>
                    <option value="todas" @selected(($filters['sucursal_id'] ?? '') === 'todas')>Todas las sucursales</option>
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" @selected((string) ($filters['sucursal_id'] ?? '') === (string) $sucursal->id)>{{ $sucursal->nombre }}{{ $sucursal->estado ? '' : ' (inactiva)' }}</option>
                    @endforeach
                </select>
                @error('sucursal_id')<p id="sucursal-error" class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
            </div>
            <button class="min-h-11 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Filtrar</button>
            <a href="{{ route('admin.ventas.index') }}" class="inline-flex min-h-11 items-center px-2 text-sm text-blue-700 focus-visible:outline-blue-600 dark:text-blue-300">Limpiar</a>
        </form>
    @endcan
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto"><table class="w-full min-w-[42rem] text-left text-sm">
            <thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><tr>
                <th scope="col" class="px-4 py-3">Venta</th><th scope="col" class="px-4 py-3">Sucursal</th><th scope="col" class="px-4 py-3">Fecha</th><th scope="col" class="px-4 py-3">Cliente</th><th scope="col" class="px-4 py-3">Vendedor</th><th scope="col" class="px-4 py-3">Pago</th><th scope="col" class="px-4 py-3 text-right">Total (Bs)</th><th scope="col" class="px-4 py-3">Ticket</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($ventas as $venta)
                <tr><td class="px-4 py-4 font-medium">#{{ $venta->id }}</td><td class="px-4 py-4">{{ $venta->sucursal->nombre }}</td><td class="px-4 py-4">{{ $venta->created_at->format('d/m/Y H:i') }}</td><td class="px-4 py-4">{{ $venta->cliente->nombre }}</td><td class="px-4 py-4">{{ $venta->usuario->nombre }}</td><td class="px-4 py-4">{{ ['efectivo' => 'Efectivo', 'qr' => 'QR', 'transferencia' => 'Transferencia'][$venta->tipo_pago] ?? $venta->tipo_pago }}</td><td class="px-4 py-4 text-right tabular-nums">{{ number_format((float) $venta->total, 2, ',', '.') }}</td><td class="px-4 py-4"><a href="{{ route('admin.ventas.show', $venta) }}" aria-label="Ver ticket de venta {{ $venta->id }}" class="py-2 text-blue-700 dark:text-blue-300">Ver ticket</a></td></tr>
                @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">No hay ventas para la sucursal seleccionada.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if($ventas->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $ventas->links() }}</div>@endif
    </div>
</div>
@endsection
