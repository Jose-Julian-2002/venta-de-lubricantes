@extends('layouts.admin')
@section('title', 'Crear proveedor')
@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-6 text-2xl font-semibold">Crear proveedor</h1>
    @include('admin.proveedores._form', ['action' => route('admin.proveedores.store')])
</div>
@endsection
