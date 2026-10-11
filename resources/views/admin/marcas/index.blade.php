@extends('layouts.admin')
@section('title', 'Marcas')
@section('content')
<div class="mx-auto max-w-6xl">
<div class="mb-6 flex flex-wrap items-center justify-between gap-4"><div><h1 class="text-2xl font-semibold">Marcas</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Organiza el catálogo de productos.</p></div>
@can('marcas.crear')<a href="{{ route('admin.marcas.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Crear marca</a>@endcan</div>
@error('nombre')<p role="alert" class="mb-4 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"><div class="overflow-x-auto">
<table class="w-full min-w-[40rem] text-left text-sm"><thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><tr>
<th scope="col" class="px-4 py-3">Nombre</th><th scope="col" class="px-4 py-3">Productos</th><th scope="col" class="px-4 py-3">Acciones</th>
</tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-800">
@forelse ($marcas as $marca)<tr>
<td class="px-4 py-4 font-medium">{{ $marca->nombre }}</td>
<td class="px-4 py-4">{{ $marca->productos_count }}</td>
<td class="px-4 py-4"><div class="flex gap-3">
@can('marcas.editar')<a href="{{ route('admin.marcas.edit', $marca) }}" aria-label="Editar {{ $marca->nombre }}" class="py-2 text-blue-700 dark:text-blue-300">Editar</a>@endcan
@can('marcas.eliminar')<form action="{{ route('admin.marcas.destroy', $marca) }}" method="POST" onsubmit="return confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')">@csrf @method('DELETE')<button @disabled($marca->productos_count > 0) aria-label="Eliminar {{ $marca->nombre }}" title="Solo se pueden eliminar registros sin productos asociados" class="py-2 text-rose-700 disabled:cursor-not-allowed disabled:text-slate-400 dark:text-rose-300">Eliminar</button></form>@endcan
</div></td></tr>
@empty<tr><td colspan="3" class="px-4 py-10 text-center text-slate-500">Todavía no hay registros.</td></tr>@endforelse
</tbody></table></div>
@if ($marcas->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $marcas->links() }}</div>@endif
</div></div>
@endsection