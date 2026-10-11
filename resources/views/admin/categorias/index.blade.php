@extends('layouts.admin')
@section('title', 'Categorías')
@section('content')
<div class="mx-auto max-w-6xl">
<div class="mb-6 flex flex-wrap items-center justify-between gap-4"><div><h1 class="text-2xl font-semibold">Categorías</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Organiza el catálogo de productos.</p></div>
@can('categorias.crear')<a href="{{ route('admin.categorias.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Crear categoría</a>@endcan</div>
@error('nombre')<p role="alert" class="mb-4 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"><div class="overflow-x-auto">
<table class="w-full min-w-[40rem] text-left text-sm"><thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><tr>
<th scope="col" class="px-4 py-3">Nombre</th><th scope="col" class="px-4 py-3">Productos</th><th scope="col" class="px-4 py-3">Acciones</th>
</tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-800">
@forelse ($categorias as $categoria)<tr>
<td class="px-4 py-4 font-medium">{{ $categoria->nombre }}</td>
<td class="px-4 py-4">{{ $categoria->productos_count }}</td>
<td class="px-4 py-4"><div class="flex gap-3">
@can('categorias.editar')<a href="{{ route('admin.categorias.edit', $categoria) }}" aria-label="Editar {{ $categoria->nombre }}" class="py-2 text-blue-700 dark:text-blue-300">Editar</a>@endcan
@can('categorias.eliminar')<form action="{{ route('admin.categorias.destroy', $categoria) }}" method="POST" onsubmit="return confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')">@csrf @method('DELETE')<button @disabled($categoria->productos_count > 0) aria-label="Eliminar {{ $categoria->nombre }}" title="Solo se pueden eliminar registros sin productos asociados" class="py-2 text-rose-700 disabled:cursor-not-allowed disabled:text-slate-400 dark:text-rose-300">Eliminar</button></form>@endcan
</div></td></tr>
@empty<tr><td colspan="3" class="px-4 py-10 text-center text-slate-500">Todavía no hay registros.</td></tr>@endforelse
</tbody></table></div>
@if ($categorias->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $categorias->links() }}</div>@endif
</div></div>
@endsection