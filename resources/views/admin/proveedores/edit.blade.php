@extends('layouts.admin')
@section('title', 'Editar proveedor')
@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-6 text-2xl font-semibold">Editar proveedor</h1>
    @include('admin.proveedores._form', ['action' => route('admin.proveedores.update', $proveedor), 'method' => 'PUT'])
</div>
@endsection
