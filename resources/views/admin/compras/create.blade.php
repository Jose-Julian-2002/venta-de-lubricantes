@extends('layouts.admin')
@section('title', 'Registrar compra')
@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-semibold">Registrar compra</h1><p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Ingresa las cajas recibidas. Las unidades se sumarán al inventario al registrar la compra.</p></div>
        @can('compras.ver')<a href="{{ route('admin.compras.index') }}" class="py-2 text-sm text-blue-700 dark:text-blue-300">Ver historial de compras</a>@endcan
    </div>
    @include('admin.compras._form', ['action' => route('admin.compras.store')])
</div>
@endsection
