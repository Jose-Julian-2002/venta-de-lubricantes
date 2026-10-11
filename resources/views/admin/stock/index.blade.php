@extends('layouts.admin')
@section('title', 'Stock y Kardex')
@section('content')
<div class="mx-auto max-w-6xl">
    <h1 class="text-2xl font-semibold">Stock y Kardex</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Existencias centralizadas en unidades sueltas. Consulta las entradas y su trazabilidad por producto.</p>
    <form action="{{ route('admin.stock.index') }}" method="GET" class="my-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div><label for="buscar" class="mb-2 block text-sm font-medium">Producto</label><input id="buscar" name="buscar" value="{{ $filters['buscar'] ?? '' }}" maxlength="150" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Nombre del producto"></div>
        @foreach (['categoria' => 'Categoría', 'marca' => 'Marca'] as $filter => $label)
            <div><label for="{{ $filter }}_id" class="mb-2 block text-sm font-medium">{{ $label }}</label>
                <select id="{{ $filter }}_id" name="{{ $filter }}_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    <option value="">Todas</option>
                    @foreach ($filter === 'categoria' ? $categorias : $marcas as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters[$filter.'_id'] ?? '') === (string) $option->id)>{{ $option->nombre }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
        <div class="flex items-end gap-3"><button class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">Filtrar</button><a href="{{ route('admin.stock.index') }}" class="px-2 py-2.5 text-sm text-blue-700 dark:text-blue-300">Limpiar</a></div>
    </form>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[40rem] text-left text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800"><tr>@foreach (['Producto', 'Marca', 'Categoría', 'Stock (unidades)', 'Estado', 'Historial'] as $label)<th scope="col" class="px-4 py-3">{{ $label }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($productos as $producto)
                    <tr><td class="px-4 py-4 font-medium">{{ $producto->nombre }}</td><td class="px-4 py-4">{{ $producto->marca->nombre }}</td><td class="px-4 py-4">{{ $producto->categoria->nombre }}</td><td class="px-4 py-4 tabular-nums">{{ $producto->stock_actual }}</td><td class="px-4 py-4">{{ $producto->estado ? 'Activo' : 'Inactivo' }}</td><td class="px-4 py-4"><a href="{{ route('admin.stock.kardex', $producto) }}" class="text-blue-700 dark:text-blue-300">Ver Kardex<span class="sr-only"> de {{ $producto->nombre }}</span></a></td></tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No se encontraron productos.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($productos->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $productos->links() }}</div>@endif
    </div>
</div>
@endsection
