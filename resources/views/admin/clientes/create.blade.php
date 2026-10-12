@extends('layouts.admin')
@section('title', 'Crear cliente')
@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-6 text-2xl font-semibold">Crear cliente</h1>
    @include('admin.clientes._form', ['action' => route('admin.clientes.store')])
</div>
@endsection
