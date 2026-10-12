@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div><h1 class="text-2xl font-semibold">Dashboard</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Resumen mensual de tu negocio.</p></div>
        @if ($resumen)
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-end gap-3">
            <div><label for="mes" class="mb-2 block text-sm font-medium">Mes del resumen</label><input id="mes" name="mes" type="month" required value="{{ $resumen['mes'] }}" class="min-h-11 rounded-lg border border-slate-300 bg-white px-3 dark:border-slate-700 dark:bg-slate-900">@error('mes')<p class="mt-1 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror</div>
            <button type="submit" class="min-h-11 rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Consultar</button>
        </form>
        @endif
    </div>
    @if ($resumen)
    <section aria-labelledby="resultados-title">
        <h2 id="resultados-title" class="mb-4 text-lg font-semibold">Resultados del mes</h2>
        <dl class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><dt class="text-sm text-slate-600 dark:text-slate-400">Ventas totales</dt><dd class="mt-3 break-words text-2xl font-semibold tabular-nums">Bs {{ number_format((float) $resumen['ventasTotal'], 2, ',', '.') }}</dd><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $resumen['ventasCantidad'] }} ventas completadas</p></div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><dt class="text-sm text-slate-600 dark:text-slate-400">Ganancia bruta estimada</dt><dd class="mt-3 break-words text-2xl font-semibold tabular-nums">{{ $resumen['gananciaTotal'] === null ? 'Pendiente' : 'Bs '.number_format((float) $resumen['gananciaTotal'], 2, ',', '.') }}</dd><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Ventas menos costo de las unidades vendidas</p></div>
        </dl>
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            @foreach (['marcasTop' => 'Top 5 marcas', 'productosTop' => 'Top 5 productos'] as $rankingKey => $rankingTitle)
                <section aria-labelledby="{{ $rankingKey }}-title" class="min-w-0 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 id="{{ $rankingKey }}-title" class="text-lg font-semibold">{{ $rankingTitle }}</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Unidades vendidas en el mes</p>
                    <ol class="mt-6 space-y-5">
                        @forelse ($resumen[$rankingKey] as $item)
                            <li>
                                <div class="mb-2 flex items-start justify-between gap-4">
                                    <div class="min-w-0"><p class="break-words text-sm font-medium">{{ $loop->iteration }}. {{ $item['nombre'] }}</p>@isset($item['marca'])<p class="mt-1 break-words text-xs text-slate-600 dark:text-slate-400">{{ $item['marca'] }}</p>@endisset</div>
                                    <span class="shrink-0 text-sm font-semibold tabular-nums">{{ number_format($item['unidades'], 0, ',', '.') }} <span class="font-normal text-slate-600 dark:text-slate-400">uds.</span></span>
                                </div>
                                <div aria-hidden="true" class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400" style="width: {{ round($item['unidades'] / max(1, $resumen[$rankingKey][0]['unidades']) * 100, 2) }}%"></div>
                                </div>
                            </li>
                        @empty
                            <li class="py-5 text-sm text-slate-600 dark:text-slate-400">No hay ventas para mostrar en este mes.</li>
                        @endforelse
                    </ol>
                </section>
            @endforeach
        </div>
        <p class="mt-4 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-400">La ganancia usa el costo de la última compra registrada antes de cada venta. No incluye gastos operativos ni impuestos. Los líderes se calculan por unidades vendidas en el mes seleccionado.</p>
        @if ($resumen['costosPendientes'] > 0)<p role="status" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">No se puede estimar la ganancia completa: {{ $resumen['costosPendientes'] }} unidades vendidas no tienen un costo de compra anterior registrado.</p>@endif
        @if ($resumen['ventasCantidad'] === 0)<p class="mt-6 rounded-xl border border-dashed border-slate-300 p-6 text-sm text-slate-600 dark:border-slate-700 dark:text-slate-400">No hay ventas completadas en este mes. Selecciona otro período o registra una venta desde el punto de venta.</p>@endif
    </section>
    @else
        <p class="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">Tu rol no tiene permiso para consultar el resumen de ventas. Usa las secciones del menú para acceder a tus tareas.</p>
    @endif
    <div class="mt-8 flex flex-wrap gap-3">
        @can('ventas.crear')<a href="{{ route('admin.ventas.create') }}" class="inline-flex min-h-11 items-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Abrir punto de venta</a>@endcan
        @can('compras.crear')<a href="{{ route('admin.compras.create') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">Registrar compra</a>@endcan
    </div>
</div>
@endsection
