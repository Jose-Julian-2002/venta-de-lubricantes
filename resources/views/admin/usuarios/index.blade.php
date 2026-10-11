@extends('layouts.admin')

@section('title', 'Usuarios')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-700">Administración</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">Usuarios</h1>
                <p class="mt-2 text-sm text-slate-600">Gestiona las cuentas y el rol de acceso de cada persona.</p>
            </div>
            @can('usuarios.crear')
                <a href="{{ route('admin.usuarios.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                    Crear usuario
                </a>
            @endcan
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[46rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Nombre</th>
                            <th scope="col" class="px-5 py-3">Correo electrónico</th>
                            <th scope="col" class="px-5 py-3">Rol</th>
                            <th scope="col" class="px-5 py-3">Estado</th>
                            <th scope="col" class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($usuarios as $usuario)
                            <tr>
                                <td class="px-5 py-4 font-medium">{{ $usuario->nombre }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $usuario->email }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $usuario->rol->nombre }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $usuario->estado ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        @can('usuarios.editar')
                                            <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="font-medium text-blue-700 hover:text-blue-900">Editar</a>
                                        @endcan
                                        @can('usuarios.eliminar')
                                            <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-rose-700 hover:text-rose-900">Eliminar</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-slate-500">Todavía no hay usuarios.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($usuarios->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $usuarios->links() }}</div>
            @endif
        </div>
    </div>
@endsection
