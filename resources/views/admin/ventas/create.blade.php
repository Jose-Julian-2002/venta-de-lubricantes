@extends('layouts.admin')
@section('title', 'Punto de venta')
@section('content')
@php
    $catalogo = $productos->map(fn ($producto) => [
        'id' => $producto->id, 'nombre' => $producto->nombre, 'marca' => $producto->marca->nombre,
        'categoria_id' => $producto->categoria_id, 'categoria' => $producto->categoria->nombre, 'marca_id' => $producto->marca_id,
        'stock' => $producto->stock_actual, 'menor' => $producto->precio_venta_menor, 'mayor' => $producto->precio_venta_mayor,
        'unidades_por_caja' => $producto->ultimaCompraDetalle?->unidades_por_caja,
    ])->values();
    $carrito = collect(old('detalles', []))->filter(fn ($row) => is_array($row))->map(fn ($row) => [
        'producto_id' => $row['producto_id'] ?? '', 'cantidad' => $row['cantidad'] ?? 1, 'tipo_precio' => $row['tipo_precio'] ?? 'menor',
        'presentacion' => $row['presentacion'] ?? 'unidad',
    ])->values();
@endphp
<form method="POST" action="{{ route('admin.ventas.store') }}" class="mx-auto max-w-6xl pb-36"
    x-data="{
        productos: {{ Illuminate\Support\Js::from($catalogo) }}, clientes: {{ Illuminate\Support\Js::from($clientes->map(fn ($cliente) => ['id' => $cliente->id, 'nombre' => $cliente->nombre])->values()) }},
        carrito: {{ Illuminate\Support\Js::from($carrito) }}, clienteId: {{ Illuminate\Support\Js::from((string) old('cliente_id', $defaultClienteId)) }},
        busqueda: '', categoriaId: '', marcaId: '', tarifa: 'menor', sending: false, creando: false, errorCliente: '', nuevoCliente: {nombre: '', nit_ci: '', telefono: ''},
        get categorias() { return [...new Map(this.productos.map(p => [p.categoria_id, {id: p.categoria_id, nombre: p.categoria}])).values()].sort((a, b) => a.nombre.localeCompare(b.nombre, 'es')); },
        get marcas() { return [...new Map(this.productos.map(p => [p.marca_id, {id: p.marca_id, nombre: p.marca}])).values()].sort((a, b) => a.nombre.localeCompare(b.nombre, 'es')); },
        get resultados() { const query = this.busqueda.trim().toLocaleLowerCase('es'); return this.productos.filter(p => (!this.categoriaId || String(p.categoria_id) === String(this.categoriaId)) && (!this.marcaId || String(p.marca_id) === String(this.marcaId)) && (p.nombre + ' ' + p.marca).toLocaleLowerCase('es').includes(query)); },
        producto(row) { return this.productos.find(p => String(p.id) === String(row.producto_id)); },
        cents(row) { const p = this.producto(row); return p ? Math.round(Number(p[row.tipo_precio] || 0) * 100) * Number(row.cantidad || 0) : 0; },
        get total() { return this.carrito.reduce((sum, row) => sum + this.cents(row), 0); },
        filaValida(row) { const p = this.producto(row); const factor = this.paso(row); return p && ['unidad', 'caja'].includes(row.presentacion) && row.tipo_precio === (row.presentacion === 'caja' ? 'mayor' : 'menor') && p[row.tipo_precio] !== null && factor > 0 && Number.isInteger(Number(row.cantidad)) && row.cantidad > 0 && row.cantidad <= p.stock && Number(row.cantidad) % factor === 0; },
        get valido() { return this.carrito.length > 0 && this.carrito.every(row => this.filaValida(row)); },
        money(cents) { return 'Bs ' + (cents / 100).toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2}); },
        factor(p) { const value = Number(p?.unidades_por_caja); return Number.isInteger(value) && value > 0 ? value : 0; },
        paso(row) { return row.presentacion === 'caja' ? this.factor(this.producto(row)) : 1; },
        precioCatalogo(p) { const factor = this.tarifa === 'mayor' ? this.factor(p) : 1; return p[this.tarifa] === null ? 'Sin precio' : factor < 1 ? 'Caja no configurada' : this.money(Math.round(Number(p[this.tarifa]) * 100) * factor); },
        disponibles(p) { const row = this.carrito.find(r => String(r.producto_id) === String(p.id)); return Number(p.stock) - Number(row?.cantidad || 0); },
        puedeAgregar(p) { const factor = this.tarifa === 'mayor' ? this.factor(p) : 1; const row = this.carrito.find(r => String(r.producto_id) === String(p.id)); return p[this.tarifa] !== null && factor > 0 && (row && row.tipo_precio !== this.tarifa ? Number(p.stock) >= factor : this.disponibles(p) >= factor) && (row || this.carrito.length < 100); },
        cambiarPresentacion(row, presentacion) { row.presentacion = presentacion; row.tipo_precio = presentacion === 'caja' ? 'mayor' : 'menor'; row.cantidad = this.paso(row); },
        agregar(p) { if (!this.puedeAgregar(p)) return; const row = this.carrito.find(r => String(r.producto_id) === String(p.id)); const presentacion = this.tarifa === 'mayor' ? 'caja' : 'unidad'; if (row) { if (row.presentacion !== presentacion) this.cambiarPresentacion(row, presentacion); else this.incrementar(row); } else { this.carrito.push({producto_id: p.id, cantidad: this.tarifa === 'mayor' ? this.factor(p) : 1, tipo_precio: this.tarifa, presentacion}); } },
        puedeIncrementar(row) { const paso = this.paso(row); return paso > 0 && Number(row.cantidad) + paso <= Number(this.producto(row)?.stock); },
        incrementar(row) { if (this.puedeIncrementar(row)) row.cantidad = Number(row.cantidad) + this.paso(row); },
        restar(row) { const paso = this.paso(row); if (paso > 0 && Number(row.cantidad) > paso) row.cantidad = Number(row.cantidad) - paso; else this.carrito = this.carrito.filter(r => r !== row); },
        async guardarCliente() {
            this.creando = true; this.errorCliente = '';
            try {
                const response = await fetch('{{ route('admin.clientes.store') }}', {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.$refs.csrf.value}, body: JSON.stringify(this.nuevoCliente)});
                const data = await response.json();
                if (!response.ok) { this.errorCliente = Object.values(data.errors || {}).flat().join(' ') || data.message || 'No se pudo guardar el cliente.'; return; }
                this.clientes.push(data.cliente); this.clienteId = String(data.cliente.id); this.$refs.dialog.close(); this.nuevoCliente = {nombre: '', nit_ci: '', telefono: ''};
            } catch (error) { this.errorCliente = 'No se pudo conectar. Revisa tu conexión e intenta de nuevo.'; }
            finally { this.creando = false; }
        }
    }" x-on:submit="if (!valido || sending) { $event.preventDefault(); } else { sending = true; }">
    @csrf
    <input type="hidden" x-ref="csrf" value="{{ csrf_token() }}">
    <input type="hidden" name="token" value="{{ old('token', (string) Illuminate\Support\Str::uuid()) }}">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-2xl font-semibold">Punto de venta</h1><p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Busca, agrega unidades y cobra.</p></div>
        @can('ventas.ver')<a href="{{ route('admin.ventas.index') }}" class="min-h-11 py-3 text-sm text-blue-700 dark:text-blue-300">Historial de ventas</a>@endcan
    </div>
    @if ($errors->any())
        <ul role="alert" class="mb-5 list-inside list-disc rounded-xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-950 dark:text-rose-200">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    @endif
    <noscript><p class="mb-5 text-rose-700">Activa JavaScript para usar el punto de venta.</p></noscript>
    <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
        <section aria-labelledby="catalogo-title">
            <h2 id="catalogo-title" class="sr-only">Catálogo de productos</h2>
            <label for="busqueda" class="mb-2 block text-sm font-medium">Buscar producto o marca</label>
            <input id="busqueda" type="search" x-model.debounce.100ms="busqueda" placeholder="Ej. edge 5W-30 o Castrol" autocomplete="off" class="min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-200 dark:border-slate-700 dark:bg-slate-900">
            <div class="mt-4 grid grid-cols-2 gap-3">
                <div><label for="filtro-categoria" class="mb-2 block text-sm font-medium">Categoría</label><select id="filtro-categoria" x-model="categoriaId" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 dark:border-slate-700 dark:bg-slate-900"><option value="">Todas las categorías</option><template x-for="categoria in categorias" :key="categoria.id"><option :value="categoria.id" x-text="categoria.nombre"></option></template></select></div>
                <div><label for="filtro-marca" class="mb-2 block text-sm font-medium">Marca</label><select id="filtro-marca" x-model="marcaId" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 dark:border-slate-700 dark:bg-slate-900"><option value="">Todas las marcas</option><template x-for="marca in marcas" :key="marca.id"><option :value="marca.id" x-text="marca.nombre"></option></template></select></div>
            </div>
            <div class="my-4 flex items-center justify-between gap-3">
                <label for="tarifa" class="text-sm">Presentación para agregar</label>
                <select id="tarifa" x-model="tarifa" class="min-h-11 rounded-lg border border-slate-300 bg-white px-3 dark:border-slate-700 dark:bg-slate-900"><option value="menor">Unidad</option><option value="mayor">Caja</option></select>
            </div>
            <p class="mb-3 text-xs text-slate-600 dark:text-slate-400">El precio de caja incluye todas sus unidades. Cambiar la presentación reinicia la cantidad a una unidad o una caja.</p>
            <div class="divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200 bg-white dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
                <template x-for="p in resultados" :key="p.id">
                    <button type="button" x-on:click="agregar(p)" :disabled="!puedeAgregar(p)" class="flex min-h-24 w-full items-center justify-between gap-3 p-4 text-left hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-blue-600 disabled:opacity-60 dark:hover:bg-slate-800">
                        <span class="min-w-0"><span class="block text-sm text-slate-600 dark:text-slate-400" x-text="p.marca"></span><span class="block break-words font-medium" x-text="p.nombre"></span><span class="mt-1 block text-xs" x-text="p.stock + ' unidades en stock'"></span></span>
                        <span class="shrink-0 text-right"><span class="block font-semibold tabular-nums" x-text="precioCatalogo(p)"></span><span class="block text-xs text-slate-600 dark:text-slate-400" x-text="tarifa === 'mayor' && factor(p) > 0 ? 'Caja de ' + factor(p) + ' unidades' : 'Por unidad'"></span><span class="block text-xs text-blue-700 dark:text-blue-300" x-text="puedeAgregar(p) ? 'Agregar' : (tarifa === 'mayor' && factor(p) < 1 ? 'Sin compra registrada' : (p[tarifa] === null ? 'Sin precio' : 'Stock insuficiente'))"></span></span>
                    </button>
                </template>
                <p x-show="resultados.length === 0" class="p-6 text-sm text-slate-600 dark:text-slate-400">No hay productos que coincidan con la búsqueda.</p>
            </div>
        </section>
        <section aria-labelledby="carrito-title" class="min-w-0">
            <div class="mb-4 flex items-center justify-between"><h2 id="carrito-title" class="text-lg font-semibold">Carrito</h2><span class="text-sm text-slate-600 dark:text-slate-400" x-text="carrito.length + ' productos'"></span></div>
            <div class="mb-5 flex items-end gap-2"><div class="min-w-0 flex-1"><label for="cliente" class="mb-2 block text-sm font-medium">Cliente</label><select id="cliente" name="cliente_id" x-model="clienteId" required class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 dark:border-slate-700 dark:bg-slate-900"><template x-for="cliente in clientes" :key="cliente.id"><option :value="cliente.id" x-text="cliente.nombre"></option></template></select></div><button type="button" x-on:click="$refs.dialog.showModal()" class="min-h-11 rounded-lg border border-blue-700 px-3 text-sm text-blue-700 dark:border-blue-400 dark:text-blue-300">Nuevo cliente</button></div>
            <p x-show="carrito.length === 0" class="rounded-xl border border-dashed border-slate-300 p-6 text-sm text-slate-600 dark:border-slate-700 dark:text-slate-400">Agrega un producto para comenzar la venta.</p>
            <div class="space-y-4">
                <template x-for="(row, index) in carrito" :key="row.producto_id">
                    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <input type="hidden" :name="'detalles[' + index + '][producto_id]'" :value="row.producto_id">
                        <input type="hidden" :name="'detalles[' + index + '][cantidad]'" :value="row.cantidad">
                        <input type="hidden" :name="'detalles[' + index + '][tipo_precio]'" :value="row.tipo_precio">
                        <div class="flex items-start justify-between gap-2"><h3 class="break-words text-sm font-medium" x-text="producto(row) ? producto(row).marca + ' · ' + producto(row).nombre : 'Producto no disponible'"></h3><button type="button" x-on:click="carrito.splice(index, 1)" class="min-h-11 shrink-0 px-2 text-xs text-rose-700 dark:text-rose-300" aria-label="Quitar producto">Quitar</button></div>
                        <label :for="'presentacion-' + index" class="sr-only">Presentación</label><select :id="'presentacion-' + index" :name="'detalles[' + index + '][presentacion]'" x-model="row.presentacion" x-on:change="cambiarPresentacion(row, $event.target.value)" class="mb-3 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-2 text-sm dark:border-slate-700 dark:bg-slate-950"><option value="unidad" :disabled="!producto(row) || producto(row)?.menor === null">Unidad</option><option value="caja" :disabled="!producto(row) || factor(producto(row)) < 1 || producto(row)?.mayor === null">Caja</option></select>
                        <div class="flex items-center justify-between gap-3"><div class="flex items-center rounded-lg border border-slate-300 dark:border-slate-700"><button type="button" x-on:click="restar(row)" class="min-h-11 min-w-11 text-xl" :aria-label="row.presentacion === 'caja' ? 'Restar una caja' : 'Restar una unidad'">−</button><span class="min-w-9 text-center tabular-nums" x-text="row.presentacion === 'caja' && paso(row) > 0 ? Number(row.cantidad) / paso(row) : row.cantidad"></span><button type="button" x-on:click="incrementar(row)" :disabled="!puedeIncrementar(row)" class="min-h-11 min-w-11 text-xl disabled:opacity-40" :aria-label="row.presentacion === 'caja' ? 'Sumar una caja' : 'Sumar una unidad'">+</button></div><span class="font-semibold tabular-nums" x-text="money(cents(row))"></span></div>
                        <p class="mt-2 text-xs text-slate-600 dark:text-slate-400" x-text="row.presentacion === 'caja' && paso(row) > 0 ? (Number(row.cantidad) / paso(row)) + ' cajas · ' + row.cantidad + ' unidades · ' + money(Math.round(Number(producto(row)?.mayor || 0) * 100) * paso(row)) + ' / caja' : row.cantidad + ' unidades'"></p>
                        <p x-show="!filaValida(row)" class="mt-2 text-sm text-rose-700 dark:text-rose-300">Revisa el stock, el precio o las unidades por caja antes de cobrar.</p>
                    </div>
                </template>
            </div>
            <label for="tipo_pago" class="mb-2 mt-5 block text-sm font-medium">Método de pago</label><select id="tipo_pago" name="tipo_pago" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 dark:border-slate-700 dark:bg-slate-900">@foreach (['efectivo' => 'Efectivo', 'qr' => 'QR', 'transferencia' => 'Transferencia'] as $value => $label)<option value="{{ $value }}" @selected(old('tipo_pago', 'efectivo') === $value)>{{ $label }}</option>@endforeach</select>
            <p class="mt-3 text-xs text-slate-600 dark:text-slate-400">El stock y los precios se verifican nuevamente al finalizar.</p>
        </section>
    </div>
    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white px-4 pt-3 pb-[max(1rem,env(safe-area-inset-bottom))] dark:border-slate-700 dark:bg-slate-900"><div class="mx-auto flex max-w-6xl items-center justify-between gap-4"><div><span class="block text-xs text-slate-600 dark:text-slate-400">Total a cobrar</span><span aria-live="polite" class="text-xl font-semibold tabular-nums" x-text="money(total)"></span></div><button type="submit" :disabled="!valido || sending" class="min-h-14 min-w-0 flex-1 rounded-xl bg-blue-700 px-3 text-sm font-semibold sm:max-w-sm sm:px-6 sm:text-base text-white hover:bg-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-50" x-text="sending ? 'Procesando…' : 'Cobrar / Finalizar venta'">Cobrar / Finalizar venta</button></div></div>
    <dialog x-ref="dialog" x-on:close="errorCliente = ''" x-on:keydown.enter.prevent="if (!creando && nuevoCliente.nombre.trim()) guardarCliente()" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-xl bg-white p-6 text-slate-900 backdrop:bg-black/50 dark:bg-slate-900 dark:text-slate-100" aria-labelledby="nuevo-cliente-title">
        <h2 id="nuevo-cliente-title" class="text-lg font-semibold">Nuevo cliente</h2><p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Se seleccionará para esta venta.</p>
        <p x-show="errorCliente" x-text="errorCliente" role="alert" class="mt-3 text-sm text-rose-700 dark:text-rose-300"></p>
        <label for="cliente-nombre" class="mb-2 mt-4 block text-sm">Nombre</label><input id="cliente-nombre" x-model="nuevoCliente.nombre" maxlength="150" class="min-h-11 w-full rounded-lg border border-slate-300 bg-transparent px-3 dark:border-slate-700">
        <label for="cliente-nit" class="mb-2 mt-4 block text-sm">NIT / CI (opcional)</label><input id="cliente-nit" x-model="nuevoCliente.nit_ci" maxlength="50" class="min-h-11 w-full rounded-lg border border-slate-300 bg-transparent px-3 dark:border-slate-700">
        <label for="cliente-telefono" class="mb-2 mt-4 block text-sm">Teléfono (opcional)</label><input id="cliente-telefono" type="tel" x-model="nuevoCliente.telefono" maxlength="30" class="min-h-11 w-full rounded-lg border border-slate-300 bg-transparent px-3 dark:border-slate-700">
        <div class="mt-5 flex justify-end gap-3"><button type="button" x-on:click="$refs.dialog.close()" :disabled="creando" class="min-h-11 px-3">Cancelar</button><button type="button" x-on:click="guardarCliente()" :disabled="creando || !nuevoCliente.nombre.trim()" class="min-h-11 rounded-lg bg-blue-700 px-4 font-medium text-white disabled:opacity-50" x-text="creando ? 'Guardando…' : 'Guardar cliente'">Guardar cliente</button></div>
    </dialog>
</form>
@endsection
