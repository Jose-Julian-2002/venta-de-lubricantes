@extends('layouts.admin')
@section('title', 'Editar sucursal')
@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-6 text-2xl font-semibold">Editar sucursal</h1>
    @include('admin.sucursales._form', ['action' => route('admin.sucursales.update', $sucursal), 'method' => 'PUT'])
</div>
@endsection
