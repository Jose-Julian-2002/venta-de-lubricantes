<div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4 dark:border-slate-800">
    <p class="text-sm text-slate-600 dark:text-slate-300">Sucursal de trabajo: <strong class="text-slate-900 dark:text-white">{{ $sucursalActual->nombre }}</strong></p>
    @can('sucursales.ver')
        <form action="{{ route('admin.sucursal-actual.store') }}" method="POST" class="flex flex-wrap items-center gap-2">
            @csrf
            <label for="sucursal-actual" class="sr-only">Seleccionar sucursal de trabajo</label>
            <select id="sucursal-actual" name="sucursal_id" class="min-h-11 max-w-56 rounded-lg border border-slate-300 bg-white px-3 text-sm focus-visible:outline-blue-600 dark:border-slate-700 dark:bg-slate-900">
                @foreach ($sucursalesDisponibles as $opcionSucursal)
                    <option value="{{ $opcionSucursal->id }}" @selected($sucursalActual->id === $opcionSucursal->id)>{{ $opcionSucursal->nombre }}</option>
                @endforeach
            </select>
            <button class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm font-medium hover:bg-slate-100 focus-visible:outline-blue-600 dark:border-slate-700 dark:hover:bg-slate-800">Cambiar sucursal</button>
        </form>
    @endcan
</div>
