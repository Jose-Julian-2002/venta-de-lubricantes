@extends('layouts.admin')
@section('title', 'Sucursales')
@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-semibold">Sucursales</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Compara la actividad acumulada de cada local. El catálogo, los clientes y los proveedores son compartidos.</p></div>
        @can('sucursales.crear')<a href="{{ route('admin.sucursales.create') }}" class="min-h-11 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Crear sucursal</a>@endcan
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto"><table class="w-full min-w-[55rem] text-left text-sm">
            <thead class="bg-slate-100 dark:bg-slate-800"><tr>
                @foreach (['Sucursal', 'Usuarios', 'Stock (unidades)', 'Ventas acumuladas (Bs)', 'Compras acumuladas (Bs)', 'Estado', 'Acciones'] as $label)<th scope="col" class="px-4 py-3">{{ $label }}</th>@endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($sucursales as $sucursal)
                    <tr>
                        <td class="px-4 py-4"><span class="font-medium">{{ $sucursal->nombre }}</span>@if($sucursal->direccion)<span class="mt-1 block text-xs text-slate-600 dark:text-slate-400">{{ $sucursal->direccion }}</span>@endif</td>
                        <td class="px-4 py-4 tabular-nums">{{ $sucursal->usuarios_count }}</td>
                        <td class="px-4 py-4 tabular-nums">{{ $sucursal->unidades_stock ?? 0 }}</td>
                        <td class="px-4 py-4 tabular-nums">{{ number_format((float) $sucursal->ventas_total, 2, ',', '.') }}</td>
                        <td class="px-4 py-4 tabular-nums">{{ number_format((float) $sucursal->compras_total, 2, ',', '.') }}</td>
                        <td class="px-4 py-4">{{ $sucursal->estado ? 'Activa' : 'Inactiva' }}</td>
                        <td class="px-4 py-4"><div class="flex flex-wrap items-center gap-3">
                            @if($sucursal->estado)
                                <form action="{{ route('admin.sucursal-actual.store') }}" method="POST">@csrf<input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}"><button class="min-h-11 text-blue-700 focus-visible:outline-blue-600 dark:text-blue-300" aria-label="Consultar {{ $sucursal->nombre }}">Consultar</button></form>
                            @endif
                            @can('sucursales.editar')<a href="{{ route('admin.sucursales.edit', $sucursal) }}" class="inline-flex min-h-11 items-center text-blue-700 focus-visible:outline-blue-600 dark:text-blue-300" aria-label="Editar {{ $sucursal->nombre }}">Editar</a>@endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-600 dark:text-slate-400">No hay sucursales registradas.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="mt-5">{{ $sucursales->links() }}</div>
</div>
@endsection
