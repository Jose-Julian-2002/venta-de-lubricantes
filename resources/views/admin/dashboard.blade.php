@extends('layouts.admin')

@section('title', 'Panel')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-7">
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Hola, {{ auth()->user()->nombre }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Accede directamente a las tareas que tienes autorizadas.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @can('roles.ver')
                <a href="{{ route('admin.roles.index') }}" class="group flex min-h-28 items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-blue-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-800">
                    <div class="flex items-center gap-4">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                            <x-icon name="shield" class="size-5" />
                        </span>
                        <div>
                            <h2 class="font-semibold group-hover:text-blue-700 dark:group-hover:text-blue-300">Roles y permisos</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">Administra perfiles y su acceso.</p>
                        </div>
                    </div>
                    <x-icon name="arrow-right" class="size-5 shrink-0 text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-blue-700 dark:text-slate-500 dark:group-hover:text-blue-300" />
                </a>
            @endcan
            @can('usuarios.ver')
                <a href="{{ route('admin.usuarios.index') }}" class="group flex min-h-28 items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-blue-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-800">
                    <div class="flex items-center gap-4">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                            <x-icon name="users" class="size-5" />
                        </span>
                        <div>
                            <h2 class="font-semibold group-hover:text-blue-700 dark:group-hover:text-blue-300">Usuarios</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">Administra cuentas, roles y estado.</p>
                        </div>
                    </div>
                    <x-icon name="arrow-right" class="size-5 shrink-0 text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-blue-700 dark:text-slate-500 dark:group-hover:text-blue-300" />
                </a>
            @endcan
            @foreach (['categorias' => 'Categorías', 'marcas' => 'Marcas', 'productos' => 'Productos', 'proveedores' => 'Proveedores', 'compras' => 'Compras', 'stock' => 'Stock y Kardex'] as $catalogRoute => $catalogLabel)
                @can($catalogRoute.'.ver')
                    <a href="{{ route('admin.'.$catalogRoute.'.index') }}" class="flex min-h-28 items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 hover:border-blue-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-800">
                        <div><h2 class="font-semibold">{{ $catalogLabel }}</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ match () { 'compras' => 'Consulta las compras y registra entradas.', 'stock' => 'Consulta existencias y movimientos.', 'proveedores' => 'Administra los datos de tus proveedores.', default => 'Gestiona el catálogo de productos.' } }}</p></div>
                        <x-icon name="arrow-right" class="size-5" />
                    </a>
                @endcan
            @endforeach
        </div>

        @can('compras.crear')
            @cannot('compras.ver')
                <a href="{{ route('admin.compras.create') }}" class="mt-5 inline-flex rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">Registrar compra</a>
            @endcannot
        @endcan
        @if (! auth()->user()->canAny(['roles.ver', 'usuarios.ver', 'categorias.ver', 'marcas.ver', 'productos.ver', 'proveedores.ver', 'compras.ver', 'compras.crear', 'stock.ver']))
            <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-200">Tu rol aún no tiene permisos de administración asignados.</p>
        @endif
    </div>
@endsection
