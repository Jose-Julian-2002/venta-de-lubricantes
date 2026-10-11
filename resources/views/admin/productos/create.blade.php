@extends('layouts.admin')
@section('title', 'Crear producto')
@section('content')
<div class="mx-auto max-w-3xl">
<h1 class="mb-6 text-2xl font-semibold">Crear producto</h1>
@include('admin.productos._form', ['action' => route('admin.productos.store')])
</div>
@endsection
