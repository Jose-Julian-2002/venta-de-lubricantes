<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administración') | {{ config('app.name', 'POS Lubricantes') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
<div class="min-h-screen md:flex">
    <aside class="hidden w-64 shrink-0 flex-col bg-slate-950 text-white md:flex">
        <a href="{{ route('admin.dashboard') }}" class="border-b border-white/10 px-6 py-6 text-lg font-semibold">
            POS Lubricantes
            <span class="mt-1 block text-xs font-normal text-slate-400">Administración</span>
        </a>
        <nav aria-label="Navegación principal" class="flex flex-1 flex-col gap-1 px-3 py-5">
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
                Panel
            </a>
            @can('roles.ver')
                <a href="{{ route('admin.roles.index') }}" class="rounded-lg px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
                    Roles
                </a>
            @endcan
            @can('usuarios.ver')
                <a href="{{ route('admin.usuarios.index') }}" class="rounded-lg px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
                    Usuarios
                </a>
            @endcan
        </nav>
        <div class="border-t border-white/10 px-6 py-4 text-xs text-slate-400">
            Sesión: {{ auth()->user()->nombre }}
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="border-b border-slate-200 bg-white">
            <div class="flex min-h-16 items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <details class="relative md:hidden">
                    <summary class="cursor-pointer list-none rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium">
                        Menú
                    </summary>
                    <nav aria-label="Navegación móvil" class="absolute left-0 top-12 z-10 grid min-w-48 gap-1 rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-slate-100">Panel</a>
                        @can('roles.ver')
                            <a href="{{ route('admin.roles.index') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-slate-100">Roles</a>
                        @endcan
                        @can('usuarios.ver')
                            <a href="{{ route('admin.usuarios.index') }}" class="rounded-lg px-3 py-2 text-sm hover:bg-slate-100">Usuarios</a>
                        @endcan
                    </nav>
                </details>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">@yield('title', 'Administración')</p>
                    <p class="hidden text-xs text-slate-500 sm:block">Gestión del sistema</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="hidden text-sm text-slate-600 sm:block">{{ auth()->user()->nombre }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-600">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="w-full flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    Revisa los campos señalados antes de continuar.
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
