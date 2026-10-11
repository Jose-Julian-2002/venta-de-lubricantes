@extends('layouts.admin')

@section('title', 'Crear usuario')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('admin.usuarios.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">← Volver a usuarios</a>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight">Crear usuario</h1>
        </div>
        @include('admin.usuarios._form', [
            'action' => route('admin.usuarios.store'),
            'roles' => $roles,
        ])
    </div>
@endsection
