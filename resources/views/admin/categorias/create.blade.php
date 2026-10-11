@extends('layouts.admin')
@section('title', 'Crear categoría')
@section('content')
<div class="mx-auto max-w-3xl">
<h1 class="mb-6 text-2xl font-semibold">Crear categoría</h1>
@include('admin.categorias._form', ['action' => route('admin.categorias.store')])
</div>
@endsection
