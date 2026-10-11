@extends('layouts.admin')
@section('title', 'Editar marca')
@section('content')
<div class="mx-auto max-w-3xl">
<h1 class="mb-6 text-2xl font-semibold">Editar marca</h1>
@include('admin.marcas._form', ['action' => route('admin.marcas.update', $marca), 'method' => 'PUT'])
</div>
@endsection
