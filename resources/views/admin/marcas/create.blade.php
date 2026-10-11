@extends('layouts.admin')
@section('title', 'Crear marca')
@section('content')
<div class="mx-auto max-w-3xl">
<h1 class="mb-6 text-2xl font-semibold">Crear marca</h1>
@include('admin.marcas._form', ['action' => route('admin.marcas.store')])
</div>
@endsection
