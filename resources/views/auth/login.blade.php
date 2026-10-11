<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión | {{ config('app.name', 'POS Lubricantes') }}</title>
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-10 text-slate-900 transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100">
    <main class="relative w-full max-w-md">
        <div class="mb-4 flex justify-end">
            <button type="button" data-theme-toggle aria-label="Alternar modo claro y oscuro" aria-pressed="false" title="Cambiar tema"
                class="flex size-10 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus-visible:outline-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <x-icon name="sun" class="size-5 dark:hidden" />
                <x-icon name="moon" class="hidden size-5 dark:block" />
            </button>
        </div>
        <div class="mb-8 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">POS Lubricantes</p>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight">Bienvenido</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Ingresa con tu cuenta para continuar.</p>
        </div>

        <form action="{{ route('login.store') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
            @csrf
            <div class="grid gap-5">
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-950">
                    @error('email')
                        <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-950">
                    @error('password')
                        <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-900">
                    <x-icon name="login" class="size-[18px]" />
                    Iniciar sesión
                </button>
            </div>
        </form>
    </main>
</body>
</html>
