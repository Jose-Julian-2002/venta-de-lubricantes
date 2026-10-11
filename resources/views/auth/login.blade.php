<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión | {{ config('app.name', 'POS Lubricantes') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-10 text-slate-900">
    <main class="w-full max-w-md">
        <div class="mb-8 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-700">POS Lubricantes</p>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight">Bienvenido</h1>
            <p class="mt-2 text-sm text-slate-600">Ingresa con tu cuenta para continuar.</p>
        </div>

        <form action="{{ route('login.store') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <div class="grid gap-5">
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                    @error('email')
                        <p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                    @error('password')
                        <p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                    Iniciar sesión
                </button>
            </div>
        </form>
    </main>
</body>
</html>
