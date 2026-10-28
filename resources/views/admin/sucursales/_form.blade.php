<form action="{{ $action }}" method="POST" class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
    @csrf
    @isset($method) @method($method) @endisset
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="nombre" class="mb-2 block text-sm font-medium">Nombre</label>
            <input id="nombre" name="nombre" value="{{ old('nombre', $sucursal->nombre ?? '') }}" required maxlength="100" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus-visible:outline-blue-600 dark:border-slate-700 dark:bg-slate-950" @error('nombre') aria-invalid="true" aria-describedby="nombre-error" @enderror>
            @error('nombre')<p id="nombre-error" class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="telefono" class="mb-2 block text-sm font-medium">Teléfono (opcional)</label>
            <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $sucursal->telefono ?? '') }}" maxlength="30" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus-visible:outline-blue-600 dark:border-slate-700 dark:bg-slate-950">
            @error('telefono')<p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="direccion" class="mb-2 block text-sm font-medium">Dirección (opcional)</label>
            <input id="direccion" name="direccion" value="{{ old('direccion', $sucursal->direccion ?? '') }}" maxlength="255" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus-visible:outline-blue-600 dark:border-slate-700 dark:bg-slate-950">
            @error('direccion')<p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="estado" class="mb-2 block text-sm font-medium">Estado</label>
            <select id="estado" name="estado" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus-visible:outline-blue-600 dark:border-slate-700 dark:bg-slate-950">
                <option value="1" @selected((string) old('estado', isset($sucursal) ? (int) $sucursal->estado : 1) === '1')>Activa</option>
                <option value="0" @selected((string) old('estado', isset($sucursal) ? (int) $sucursal->estado : 1) === '0')>Inactiva</option>
            </select>
            @error('estado')<p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400">Para desactivar una sucursal, reasigna o desactiva primero sus usuarios activos. Se conserva su historial.</p>
        </div>
    </div>
    <div class="mt-8 flex justify-end gap-3">
        <a href="{{ route('admin.sucursales.index') }}" class="min-h-11 rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus-visible:outline-blue-600 dark:border-slate-700">Cancelar</a>
        <button class="min-h-11 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Guardar sucursal</button>
    </div>
</form>
