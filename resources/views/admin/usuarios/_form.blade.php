@php
    $selectedState = (string) old('estado', isset($usuario) ? (int) $usuario->estado : 1);
@endphp

<form action="{{ $action }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="nombre" class="mb-1.5 block text-sm font-medium">Nombre completo</label>
            <input id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre ?? '') }}" required maxlength="100"
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
            @error('nombre')
                <p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium">Correo electrónico</label>
            <input id="email" name="email" type="email" value="{{ old('email', $usuario->email ?? '') }}" required maxlength="150" autocomplete="email"
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
            @error('email')
                <p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="rol_id" class="mb-1.5 block text-sm font-medium">Rol</label>
            <select id="rol_id" name="rol_id" required
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                <option value="">Selecciona un rol</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id }}" @selected((string) old('rol_id', $usuario->rol_id ?? '') === (string) $rol->id)>
                        {{ $rol->nombre }}
                    </option>
                @endforeach
            </select>
            @error('rol_id')
                <p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="estado" class="mb-1.5 block text-sm font-medium">Estado</label>
            <select id="estado" name="estado" required
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                <option value="1" @selected($selectedState === '1')>Activo</option>
                <option value="0" @selected($selectedState === '0')>Inactivo</option>
            </select>
            @error('estado')
                <p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium">Contraseña</label>
            <input id="password" name="password" type="password" @required(! isset($usuario)) minlength="12" autocomplete="new-password"
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
            @if (isset($usuario))
                <p class="mt-1 text-xs text-slate-500">Déjala en blanco para conservar la contraseña actual.</p>
            @else
                <p class="mt-1 text-xs text-slate-500">Debe tener al menos 12 caracteres.</p>
            @endif
            @error('password')
                <p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium">Confirmar contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password" @required(! isset($usuario)) minlength="12" autocomplete="new-password"
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
        </div>
    </div>

    <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
        <a href="{{ route('admin.usuarios.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
        <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
            Guardar usuario
        </button>
    </div>
</form>
