@extends('layouts.admin')
@section('title', 'Proveedores')
@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-semibold">Proveedores</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Empresas y contactos para el ingreso de mercadería.</p></div>
        @can('proveedores.crear')<a href="{{ route('admin.proveedores.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Crear proveedor</a>@endcan
    </div>
    @error('nombre_empresa')<p role="alert" class="mb-4 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[48rem] text-left text-sm">
                <thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><tr>
                    <th scope="col" class="px-4 py-3">Empresa / contacto</th><th scope="col" class="px-4 py-3">Teléfono</th><th scope="col" class="px-4 py-3">NIT / CI</th><th scope="col" class="px-4 py-3">Compras</th><th scope="col" class="px-4 py-3">Acciones</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($proveedores as $proveedor)
                        <tr>
                            <td class="px-4 py-4"><p class="font-medium">{{ $proveedor->nombre_empresa }}</p><p class="mt-1 text-slate-500 dark:text-slate-400">{{ $proveedor->nombre ?? 'Sin contacto' }}</p></td>
                            <td class="px-4 py-4">{{ $proveedor->telefono ?? '—' }}</td><td class="px-4 py-4">{{ $proveedor->nit_ci ?? '—' }}</td><td class="px-4 py-4">{{ $proveedor->compras_count }}</td>
                            <td class="px-4 py-4"><div class="flex gap-3">
                                @can('proveedores.editar')<a href="{{ route('admin.proveedores.edit', $proveedor) }}" aria-label="Editar {{ $proveedor->nombre_empresa }}" class="py-2 text-blue-700 dark:text-blue-300">Editar</a>@endcan
                                @can('proveedores.eliminar')<form action="{{ route('admin.proveedores.destroy', $proveedor) }}" method="POST" onsubmit="return confirm('¿Eliminar este proveedor? Esta acción no se puede deshacer.')">
                                    @csrf @method('DELETE')
                                    <button @disabled($proveedor->compras_count > 0) aria-label="Eliminar {{ $proveedor->nombre_empresa }}" title="Solo se pueden eliminar proveedores sin compras registradas" class="py-2 text-rose-700 disabled:cursor-not-allowed disabled:text-slate-400 dark:text-rose-300">Eliminar</button>
                                </form>@endcan
                            </div></td>
                        </tr>
                    @empty<tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Todavía no hay proveedores. Registra uno para comenzar a recibir compras.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        @if ($proveedores->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $proveedores->links() }}</div>@endif
    </div>
</div>
@endsection
