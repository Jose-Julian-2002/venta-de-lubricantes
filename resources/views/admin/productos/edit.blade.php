@extends('layouts.admin')
@section('title', 'Editar producto')
@section('content')
<div class="mx-auto max-w-3xl">
<h1 class="mb-6 text-2xl font-semibold">Editar producto</h1>
@include('admin.productos._form', ['action' => route('admin.productos.update', $producto), 'method' => 'PUT'])
</div>
@endsection
