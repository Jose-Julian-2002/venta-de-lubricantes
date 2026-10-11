<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administración') | {{ config('app.name', 'POS Lubricantes') }}</title>
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100">
<div class="min-h-screen md:flex">
    <aside class="hidden w-64 shrink-0 flex-col border-r border-slate-800 bg-slate-950 text-white md:flex">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 border-b border-white/10 px-6 py-6 text-lg font-semibold">
            <span class="flex size-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-300">
                <x-icon name="shield" class="size-5" />
            </span>
            <span>
            POS Lubricantes
            <span class="mt-1 block text-xs font-normal text-slate-400">Administración</span>
            </span>
        </a>
        <nav aria-label="Navegación principal" class="flex flex-1 flex-col gap-1 px-3 py-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-200 hover:bg-white/10 focus-visible:outline-blue-400">
                <x-icon name="grid" class="size-[18px] text-slate-400" />
                Panel
            </a>
            @can('roles.ver')
                <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-200 hover:bg-white/10 focus-visible:outline-blue-400">
                    <x-icon name="shield" class="size-[18px] text-slate-400" />
                    Roles
                </a>
            @endcan
            @can('usuarios.ver')
                <a href="{{ route('admin.usuarios.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-200 hover:bg-white/10 focus-visible:outline-blue-400">
                    <x-icon name="users" class="size-[18px] text-slate-400" />
                    Usuarios
                </a>
            @endcan
        </nav>
        <div class="border-t border-white/10 px-6 py-4 text-xs text-slate-400">
            Sesión: {{ auth()->user()->nombre }}
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="flex min-h-16 items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <details class="relative md:hidden">
                    <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 focus-visible:outline-blue-600 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        <x-icon name="menu" class="size-5" />
                        <span class="sr-only">Abrir menú</span>
                    </summary>
                    <nav aria-label="Navegación móvil" class="absolute left-0 top-12 z-10 grid min-w-48 gap-1 rounded-xl border border-slate-200 bg-white p-2 shadow-lg dark:border-slate-700 dark:bg-slate-900">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
                            <x-icon name="grid" class="size-4" /> Panel
                        </a>
                        @can('roles.ver')
                            <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
                                <x-icon name="shield" class="size-4" /> Roles
                            </a>
                        @endcan
                        @can('usuarios.ver')
                            <a href="{{ route('admin.usuarios.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
                                <x-icon name="users" class="size-4" /> Usuarios
                            </a>
                        @endcan
                    </nav>
                </details>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">@yield('title', 'Administración')</p>
                    <p class="hidden text-xs text-slate-500 sm:block dark:text-slate-400">Gestión del sistema</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="hidden text-sm text-slate-600 sm:block dark:text-slate-300">{{ auth()->user()->nombre }}</span>
                    <button type="button" data-theme-toggle aria-label="Alternar modo claro y oscuro" aria-pressed="false" title="Cambiar tema"
                        class="flex size-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 focus-visible:outline-blue-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        <x-icon name="sun" class="size-5 dark:hidden" />
                        <x-icon name="moon" class="hidden size-5 dark:block" />
                    </button>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex size-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 focus-visible:outline-blue-600 sm:h-auto sm:w-auto sm:gap-2 sm:px-3 sm:py-2 sm:text-sm sm:font-medium dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                            <x-icon name="logout" class="size-[18px]" />
                            <span class="hidden sm:inline">Cerrar sesión</span>
                            <span class="sr-only sm:hidden">Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="w-full flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/60 dark:text-rose-200">
                    Revisa los campos señalados antes de continuar.
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
