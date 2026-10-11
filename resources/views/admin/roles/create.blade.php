@extends('layouts.admin')

@section('title', 'Crear rol')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('admin.roles.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">← Volver a roles</a>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight">Crear rol</h1>
        </div>
        @include('admin.roles._form', [
            'action' => route('admin.roles.store'),
            'permisos' => $permisos,
        ])
    </div>
@endsection
