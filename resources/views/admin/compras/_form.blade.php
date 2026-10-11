@php
    $initialRows = collect(old('detalles', [[]]))->map(function ($row, $index) {
        $row = is_array($row) ? $row : [];
        return [
            'key' => $index,
            'producto_id' => $row['producto_id'] ?? '',
            'cantidad_cajas' => $row['cantidad_cajas'] ?? 1,
            'unidades_por_caja' => $row['unidades_por_caja'] ?? 1,
            'precio_compra_caja' => $row['precio_compra_caja'] ?? '',
            'nuevo_precio_venta_menor' => $row['nuevo_precio_venta_menor'] ?? '',
            'nuevo_precio_venta_mayor' => $row['nuevo_precio_venta_mayor'] ?? '',
        ];
    })->values()->all();
@endphp
<form action="{{ route('admin.compras.store') }}" method="POST"
    x-data="{
        rows: {{ Illuminate\Support\Js::from($initialRows) }},
        nextKey: {{ count($initialRows) }},
        sending: false,
        add() { this.rows.push({ key: this.nextKey++, producto_id: '', cantidad_cajas: 1, unidades_por_caja: 1, precio_compra_caja: '', nuevo_precio_venta_menor: '', nuevo_precio_venta_mayor: '' }); },
        cents(row) { return Math.round((Number(row.precio_compra_caja) || 0) * 100) * (Number(row.cantidad_cajas) || 0); },
        money(cents) { return (cents / 100).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    }"
    x-on:submit="sending = true">
    @csrf
    @if ($errors->any())
        <ul role="alert" class="mb-5 list-inside list-disc rounded-lg bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-950 dark:text-rose-200">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    @endif
    @if ($proveedores->isEmpty() || $productos->isEmpty())
        <p role="status" class="mb-5 text-sm text-amber-700 dark:text-amber-300">Para registrar una compra necesitas al menos un proveedor y un producto activo.</p>
    @endif
    <div class="grid gap-5 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-900">
        <div>
            <label for="proveedor_id" class="mb-2 block text-sm font-medium">Proveedor</label>
            <select id="proveedor_id" name="proveedor_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Selecciona un proveedor</option>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" @selected((string) old('proveedor_id') === (string) $proveedor->id)>{{ $proveedor->nombre_empresa }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="fecha_compra" class="mb-2 block text-sm font-medium">Fecha de compra</label>
            <input type="datetime-local" id="fecha_compra" name="fecha_compra" required value="{{ old('fecha_compra', now()->format('Y-m-d\\TH:i')) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        </div>
    </div>
    <div class="my-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Productos de la compra</h2>
        <button type="button" x-on:click="add()" x-bind:disabled="rows.length >= 100" class="rounded-lg border border-blue-700 px-4 py-2.5 text-sm font-medium text-blue-700 disabled:opacity-50 dark:border-blue-400 dark:text-blue-300">Agregar producto</button>
    </div>
    <noscript><p class="mb-4 text-rose-700">Activa JavaScript para agregar productos y registrar la compra.</p></noscript>
    <div class="grid gap-4">
        <template x-for="(row, index) in rows" x-bind:key="row.key">
            <fieldset class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-3 dark:border-slate-800 dark:bg-slate-900">
                <legend class="px-2 text-sm font-semibold" x-text="'Producto ' + (index + 1)"></legend>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label x-bind:for="'producto-' + row.key" class="mb-2 block text-sm font-medium">Producto y marca</label>
                    <select x-bind:id="'producto-' + row.key" x-bind:name="'detalles[' + index + '][producto_id]'" x-model="row.producto_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="">Selecciona un producto</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->marca->nombre }} · {{ $producto->nombre }} ({{ $producto->stock_actual }} unidades)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label x-bind:for="'cantidad_cajas-' + row.key" class="mb-2 block text-sm font-medium">Cantidad de cajas</label>
                    <input type="number" x-bind:id="'cantidad_cajas-' + row.key" x-bind:name="'detalles[' + index + '][cantidad_cajas]'" x-model="row.cantidad_cajas" min="1" max="1000000" step="1" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                </div>
                <div>
                    <label x-bind:for="'unidades_por_caja-' + row.key" class="mb-2 block text-sm font-medium">Unidades por caja</label>
                    <input type="number" x-bind:id="'unidades_por_caja-' + row.key" x-bind:name="'detalles[' + index + '][unidades_por_caja]'" x-model="row.unidades_por_caja" min="1" max="1000000" step="1" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                </div>
                <div>
                    <label x-bind:for="'precio_compra_caja-' + row.key" class="mb-2 block text-sm font-medium">Precio de compra por caja (Bs)</label>
                    <input type="number" x-bind:id="'precio_compra_caja-' + row.key" x-bind:name="'detalles[' + index + '][precio_compra_caja]'" x-model="row.precio_compra_caja" min="0" max="99999999.99" step="0.01" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                </div>
                <div>
                    <label x-bind:for="'nuevo_precio_venta_menor-' + row.key" class="mb-2 block text-sm font-medium">Nuevo precio de venta menor (Bs)</label>
                    <input type="number" x-bind:id="'nuevo_precio_venta_menor-' + row.key" x-bind:name="'detalles[' + index + '][nuevo_precio_venta_menor]'" x-model="row.nuevo_precio_venta_menor" min="0" max="99999999.99" step="0.01" placeholder="Conservar precio actual" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                </div>
                <div>
                    <label x-bind:for="'nuevo_precio_venta_mayor-' + row.key" class="mb-2 block text-sm font-medium">Nuevo precio de venta mayor (Bs)</label>
                    <input type="number" x-bind:id="'nuevo_precio_venta_mayor-' + row.key" x-bind:name="'detalles[' + index + '][nuevo_precio_venta_mayor]'" x-model="row.nuevo_precio_venta_mayor" min="0" max="99999999.99" step="0.01" placeholder="Conservar precio actual" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                </div>
                <div class="flex items-end">
                    <button type="button" x-on:click="rows.splice(index, 1)" x-bind:disabled="rows.length === 1" class="rounded-lg px-3 py-2.5 text-sm font-medium text-rose-700 disabled:cursor-not-allowed disabled:opacity-50 dark:text-rose-300">Quitar producto</button>
                </div>
                <p class="text-sm text-slate-600 sm:col-span-2 lg:col-span-3 dark:text-slate-300">
                    Unidades de entrada: <strong x-text="(Number(row.cantidad_cajas) || 0) * (Number(row.unidades_por_caja) || 0)"></strong>
                    · Subtotal: Bs <strong x-text="money(cents(row))"></strong>
                </p>
            </fieldset>
        </template>
    </div>
    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-slate-200 pt-5 dark:border-slate-800">
        <div><p class="text-lg font-semibold">Total: Bs <span x-text="money(rows.reduce((sum, row) => sum + cents(row), 0))">0,00</span></p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Los precios de venta son opcionales. El stock se sumará en unidades.</p></div>
        <button type="submit" x-bind:disabled="sending || {{ ($proveedores->isEmpty() || $productos->isEmpty()) ? 'true' : 'false' }}" @disabled($proveedores->isEmpty() || $productos->isEmpty())
            class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-text="sending ? 'Registrando…' : 'Registrar compra'">Registrar compra</span>
        </button>
    </div>
</form>
