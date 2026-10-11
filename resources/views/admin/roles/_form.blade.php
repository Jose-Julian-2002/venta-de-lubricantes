@php
    $selectedPermissions = (array) old('permisos', isset($rol) ? $rol->permisos->modelKeys() : []);
    $selectedPermissionIds = array_map('intval', $selectedPermissions);
@endphp

<form action="{{ $action }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div>
        <label for="nombre" class="mb-1.5 block text-sm font-medium">Nombre del rol</label>
        <input id="nombre" name="nombre" value="{{ old('nombre', $rol->nombre ?? '') }}" required maxlength="50"
            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
        @error('nombre')
            <p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>
        @enderror
    </div>

    <fieldset class="mt-7">
        <legend class="text-sm font-semibold">Permisos</legend>
        <p class="mt-1 text-sm text-slate-600">Selecciona las acciones que podrán realizar los usuarios con este rol.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach ($permisos as $permiso)
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50">
                    <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                        @checked(in_array($permiso->id, $selectedPermissionIds, true))
                        class="mt-0.5 size-4 rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                    <span class="text-sm">
                        <span class="block font-medium">{{ config('permissions.catalog')[$permiso->nombre] ?? $permiso->nombre }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ $permiso->nombre }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('permisos')
            <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
        @enderror
        @error('permisos.*')
            <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
        @enderror
    </fieldset>

    <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
        <a href="{{ route('admin.roles.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
        <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
            Guardar rol
        </button>
    </div>
</form>
