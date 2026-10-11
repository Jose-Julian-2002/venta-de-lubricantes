@extends('layouts.admin')

@section('title', 'Editar usuario')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-700 hover:text-blue-900 dark:text-blue-300 dark:hover:text-blue-200">
                <x-icon name="back" class="size-4" />
                Volver a usuarios
            </a>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight">Editar usuario</h1>
        </div>
        @include('admin.usuarios._form', [
            'action' => route('admin.usuarios.update', $usuario),
            'method' => 'PUT',
            'usuario' => $usuario,
            'roles' => $roles,
        ])
    </div>
@endsection
