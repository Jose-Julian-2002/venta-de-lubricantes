@extends('layouts.admin')

@section('title', 'Panel')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-7">
            <p class="text-sm font-medium text-blue-700">Administración y seguridad</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Hola, {{ auth()->user()->nombre }}</h1>
            <p class="mt-2 text-sm text-slate-600">Accede directamente a las tareas que tienes autorizadas.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @can('roles.ver')
                <a href="{{ route('admin.roles.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold group-hover:text-blue-700">Roles y permisos</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Administra perfiles y el acceso asignado a cada uno.</p>
                        </div>
                        <span aria-hidden="true" class="text-xl text-blue-700">→</span>
                    </div>
                </a>
            @endcan
            @can('usuarios.ver')
                <a href="{{ route('admin.usuarios.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold group-hover:text-blue-700">Usuarios</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Crea cuentas, asigna un rol y controla su estado.</p>
                        </div>
                        <span aria-hidden="true" class="text-xl text-blue-700">→</span>
                    </div>
                </a>
            @endcan
        </div>

        @cannot('roles.ver')
            @cannot('usuarios.ver')
                <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    Tu rol aún no tiene permisos de administración asignados.
                </p>
            @endcannot
        @endcannot
    </div>
@endsection
