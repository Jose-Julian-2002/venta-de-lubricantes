<form action="{{ $action }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
    @csrf
    @isset($method) @method($method) @endisset
    <div class="grid gap-5 sm:grid-cols-2">
        @foreach (['nombre_empresa' => ['Empresa', 150, true], 'nombre' => ['Persona de contacto', 150, false], 'telefono' => ['Teléfono', 20, false], 'nit_ci' => ['NIT / CI', 20, false]] as $field => [$label, $maxlength, $required])
            <div>
                <label for="{{ $field }}" class="mb-2 block text-sm font-medium">{{ $label }} @if(!$required)<span class="font-normal text-slate-500">(opcional)</span>@endif</label>
                <input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'telefono' ? 'tel' : 'text' }}" value="{{ old($field, $proveedor->{$field} ?? '') }}" maxlength="{{ $maxlength }}" @required($required) class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                @error($field)<p id="{{ $field }}-error" class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
            </div>
        @endforeach
        <div class="sm:col-span-2">
            <label for="direccion" class="mb-2 block text-sm font-medium">Dirección <span class="font-normal text-slate-500">(opcional)</span></label>
            <textarea id="direccion" name="direccion" rows="3" maxlength="2000" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" @error('direccion') aria-invalid="true" aria-describedby="direccion-error" @enderror>{{ old('direccion', $proveedor->direccion ?? '') }}</textarea>
            @error('direccion')<p id="direccion-error" class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="mt-8 flex justify-end gap-3">
        <a href="{{ route('admin.proveedores.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm dark:border-slate-700">Cancelar</a>
        <button class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Guardar proveedor</button>
    </div>
</form>
