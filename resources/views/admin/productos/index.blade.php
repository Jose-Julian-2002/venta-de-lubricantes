@extends('layouts.admin')
@section('title', 'Productos')
@section('content')
<div class="mx-auto max-w-6xl">
<div class="mb-6 flex flex-wrap items-center justify-between gap-4"><div><h1 class="text-2xl font-semibold">Productos</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Consulta existencias en unidades y administra los precios del catálogo.</p></div>
@can('productos.crear')<a href="{{ route('admin.productos.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Crear producto</a>@endcan</div>
@error('nombre')<p role="alert" class="mb-4 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
<form method="GET" action="{{ route('admin.productos.index') }}" class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
<div><label for="buscar" class="mb-2 block text-sm font-medium">Buscar por nombre</label><input id="buscar" name="buscar" value="{{ $filters['buscar'] ?? '' }}" maxlength="150" placeholder="Ej. Castrol 20w50" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"></div>
<div><label for="categoria_id" class="mb-2 block text-sm font-medium">Categoría</label><select id="categoria_id" name="categoria_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"><option value="">Todas</option>@foreach ($categorias as $option)<option value="{{ $option->id }}" @selected((string) ($filters['categoria_id'] ?? '') === (string) $option->id)>{{ $option->nombre }}</option>@endforeach</select></div><div><label for="marca_id" class="mb-2 block text-sm font-medium">Marca</label><select id="marca_id" name="marca_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"><option value="">Todas</option>@foreach ($marcas as $option)<option value="{{ $option->id }}" @selected((string) ($filters['marca_id'] ?? '') === (string) $option->id)>{{ $option->nombre }}</option>@endforeach</select></div>
<div class="flex items-end gap-3"><button class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Filtrar</button><a href="{{ route('admin.productos.index') }}" class="px-2 py-2.5 text-sm text-blue-700 dark:text-blue-300">Limpiar</a></div></form>
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"><div class="overflow-x-auto">
<table class="w-full min-w-[70rem] text-left text-sm"><thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><tr>
<th scope="col" class="px-4 py-3">Nombre</th><th scope="col" class="px-4 py-3">Categoría</th><th scope="col" class="px-4 py-3">Marca</th><th scope="col" class="px-4 py-3">Stock (unidades)</th><th scope="col" class="px-4 py-3">Compra (Bs)</th><th scope="col" class="px-4 py-3">Venta menor (Bs)</th><th scope="col" class="px-4 py-3">Venta mayor (Bs)</th><th scope="col" class="px-4 py-3">Estado</th><th scope="col" class="px-4 py-3">Acciones</th>
</tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-800">
@forelse ($productos as $producto)<tr>
<td class="px-4 py-4 font-medium">{{ $producto->nombre }}</td>
<td class="px-4 py-4">{{ $producto->categoria->nombre }}</td><td class="px-4 py-4">{{ $producto->marca->nombre }}</td><td class="px-4 py-4 tabular-nums">{{ $producto->stock_actual }}</td>
<td class="px-4 py-4 tabular-nums">{{ number_format((float) $producto->precio_compra_referencia, 2, ',', '.') }}</td><td class="px-4 py-4 tabular-nums">{{ number_format((float) $producto->precio_venta_menor, 2, ',', '.') }}</td><td class="px-4 py-4 tabular-nums">{{ number_format((float) $producto->precio_venta_mayor, 2, ',', '.') }}</td>
<td class="px-4 py-4">{{ $producto->estado ? 'Activo' : 'Inactivo' }}</td>
<td class="px-4 py-4"><div class="flex gap-3">
@can('productos.editar')<a href="{{ route('admin.productos.edit', $producto) }}" aria-label="Editar {{ $producto->nombre }}" class="py-2 text-blue-700 dark:text-blue-300">Editar</a>@endcan
@can('productos.eliminar')<form action="{{ route('admin.productos.destroy', $producto) }}" method="POST" onsubmit="return confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')">@csrf @method('DELETE')<button @disabled($producto->stock_actual > 0) aria-label="Eliminar {{ $producto->nombre }}" title="Solo se pueden eliminar productos sin stock" class="py-2 text-rose-700 disabled:cursor-not-allowed disabled:text-slate-400 dark:text-rose-300">Eliminar</button></form>@endcan
</div></td></tr>
@empty<tr><td colspan="9" class="px-4 py-10 text-center text-slate-500">No se encontraron productos. Prueba otros filtros o crea un producto.</td></tr>@endforelse
</tbody></table></div>
@if ($productos->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $productos->links() }}</div>@endif
</div></div>
@endsection