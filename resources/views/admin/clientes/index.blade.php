@extends('layouts.admin')
@section('title', 'Clientes')
@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-semibold">Clientes</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Datos de contacto para identificar a los compradores en tus ventas.</p></div>
        @can('clientes.crear')<a href="{{ route('admin.clientes.create') }}" class="flex min-h-11 items-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Crear cliente</a>@endcan
    </div>
    @error('nombre')<p role="alert" class="mb-4 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
    <form action="{{ route('admin.clientes.index') }}" method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        <div class="min-w-0 flex-1"><label for="buscar" class="mb-2 block text-sm font-medium">Buscar cliente</label><input type="search" id="buscar" name="buscar" value="{{ request('buscar') }}" maxlength="150" placeholder="Nombre, NIT / CI o teléfono" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900"></div>
        <button type="submit" class="min-h-11 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Buscar</button>
        @if(request()->filled('buscar'))<a href="{{ route('admin.clientes.index') }}" class="flex min-h-11 items-center px-3 text-sm text-blue-700 dark:text-blue-300">Limpiar</a>@endif
    </form>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] text-left text-sm">
                <thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><tr>
                    <th scope="col" class="px-4 py-3">Nombre</th><th scope="col" class="px-4 py-3">NIT / CI</th><th scope="col" class="px-4 py-3">Teléfono</th><th scope="col" class="px-4 py-3">Ventas</th><th scope="col" class="px-4 py-3">Acciones</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($clientes as $cliente)
                        <tr>
                            <td class="px-4 py-4"><p class="font-medium">{{ $cliente->nombre }}</p>@if($cliente->nombre === 'Cliente General')<p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Cliente predeterminado</p>@endif</td>
                            <td class="px-4 py-4">{{ $cliente->nit_ci ?? '—' }}</td><td class="px-4 py-4">{{ $cliente->telefono ?? '—' }}</td><td class="px-4 py-4 tabular-nums">{{ $cliente->ventas_count }}</td>
                            <td class="px-4 py-4"><div class="flex gap-3">
                                @can('clientes.editar')<a href="{{ route('admin.clientes.edit', $cliente) }}" aria-label="Editar {{ $cliente->nombre }}" class="flex min-h-11 items-center px-2 text-blue-700 focus-visible:outline-blue-600 dark:text-blue-300">Editar</a>@endcan
                                @can('clientes.eliminar')<form action="{{ route('admin.clientes.destroy', $cliente) }}" method="POST" onsubmit="return confirm('¿Eliminar este cliente? Esta acción no se puede deshacer.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" @disabled($cliente->ventas_count > 0 || $cliente->nombre === 'Cliente General') aria-label="Eliminar {{ $cliente->nombre }}" title="Solo se pueden eliminar clientes sin ventas. Cliente General está protegido." class="min-h-11 px-2 text-rose-700 focus-visible:outline-rose-600 disabled:cursor-not-allowed disabled:text-slate-400 dark:text-rose-300 dark:disabled:text-slate-500">Eliminar</button>
                                </form>@endcan
                            </div></td>
                        </tr>
                    @empty<tr><td colspan="5" class="px-4 py-10 text-center text-slate-600 dark:text-slate-400">{{ request()->filled('buscar') ? 'No hay clientes que coincidan con la búsqueda.' : 'Todavía no hay clientes registrados.' }}</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        @if ($clientes->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $clientes->links() }}</div>@endif
    </div>
</div>
@endsection
