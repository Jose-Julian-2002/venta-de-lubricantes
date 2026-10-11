@php
    $selectedPermissions = (array) old('permisos', isset($rol) ? $rol->permisos->modelKeys() : []);
    $selectedPermissionIds = array_map('intval', $selectedPermissions);
@endphp

<form action="{{ $action }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div>
        <label for="nombre" class="mb-1.5 block text-sm font-medium">Nombre del rol</label>
        <input id="nombre" name="nombre" value="{{ old('nombre', $rol->nombre ?? '') }}" required maxlength="50"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-950">
        @error('nombre')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
        @enderror
    </div>

    <fieldset class="mt-7">
        <legend class="text-sm font-semibold">Permisos</legend>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Selecciona las acciones que podrán realizar los usuarios con este rol.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach ($permisos as $permiso)
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                    <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                        @checked(in_array($permiso->id, $selectedPermissionIds, true))
                        class="mt-0.5 size-4 rounded border-slate-300 text-blue-700 focus:ring-blue-600 dark:border-slate-600 dark:bg-slate-950">
                    <span class="text-sm">
                        <span class="block font-medium">{{ config('permissions.catalog')[$permiso->nombre] ?? $permiso->nombre }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $permiso->nombre }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('permisos')
            <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
        @enderror
        @error('permisos.*')
            <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
        @enderror
    </fieldset>

    <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5 dark:border-slate-800">
        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            <x-icon name="back" class="size-4" />
            Cancelar
        </a>
        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
            <x-icon name="shield" class="size-[18px]" />
            Guardar rol
        </button>
    </div>
</form>
