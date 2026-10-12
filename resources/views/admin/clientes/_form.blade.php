<form action="{{ $action }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
    @csrf
    @isset($method) @method($method) @endisset
    <div class="grid gap-5 sm:grid-cols-2">
        @foreach (['nombre' => ['Nombre', 150, true], 'nit_ci' => ['NIT / CI', 50, false], 'telefono' => ['Teléfono', 30, false]] as $field => [$label, $maxlength, $required])
            <div @class(['sm:col-span-2' => $field === 'nombre'])>
                <label for="{{ $field }}" class="mb-2 block text-sm font-medium">{{ $label }} @if(!$required)<span class="font-normal text-slate-500 dark:text-slate-400">(opcional)</span>@endif</label>
                <input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'telefono' ? 'tel' : 'text' }}" value="{{ $field === 'nombre' && ($cliente->nombre ?? '') === 'Cliente General' ? $cliente->nombre : old($field, $cliente->{$field} ?? '') }}" maxlength="{{ $maxlength }}" @required($required) @readonly($field === 'nombre' && ($cliente->nombre ?? '') === 'Cliente General') class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 read-only:bg-slate-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:read-only:bg-slate-800" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                @if($field === 'nombre' && ($cliente->nombre ?? '') === 'Cliente General')<p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Cliente General se utiliza por defecto en el punto de venta. Su nombre está protegido.</p>@endif
                @error($field)<p id="{{ $field }}-error" role="alert" class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
    <div class="mt-8 flex flex-wrap justify-end gap-3">
        <a href="{{ route(auth()->user()->can('clientes.ver') ? 'admin.clientes.index' : 'admin.dashboard') }}" class="flex min-h-11 items-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm hover:bg-slate-50 focus-visible:outline-blue-600 dark:border-slate-700 dark:hover:bg-slate-800">Cancelar</a>
        <button type="submit" class="min-h-11 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400">Guardar cliente</button>
    </div>
</form>
