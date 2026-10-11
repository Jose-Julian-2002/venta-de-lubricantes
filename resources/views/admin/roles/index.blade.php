@extends('layouts.admin')

@section('title', 'Roles')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-700">Administración</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">Roles</h1>
                <p class="mt-2 text-sm text-slate-600">Define perfiles y los permisos disponibles para cada uno.</p>
            </div>
            @can('roles.crear')
                <a href="{{ route('admin.roles.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                    Crear rol
                </a>
            @endcan
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Nombre</th>
                            <th scope="col" class="px-5 py-3">Usuarios</th>
                            <th scope="col" class="px-5 py-3">Permisos</th>
                            <th scope="col" class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($roles as $rol)
                            <tr>
                                <td class="px-5 py-4 font-medium">{{ $rol->nombre }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $rol->usuarios_count }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $rol->permisos_count }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        @can('roles.editar')
                                            <a href="{{ route('admin.roles.edit', $rol) }}" class="font-medium text-blue-700 hover:text-blue-900">Editar</a>
                                        @endcan
                                        @can('roles.eliminar')
                                            <form action="{{ route('admin.roles.destroy', $rol) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" @disabled($rol->usuarios_count > 0)
                                                    class="font-medium text-rose-700 hover:text-rose-900 disabled:cursor-not-allowed disabled:text-slate-400"
                                                    @if ($rol->usuarios_count > 0) title="El rol tiene usuarios asignados" @endif>
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-slate-500">Todavía no hay roles.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($roles->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $roles->links() }}</div>
            @endif
        </div>
    </div>
@endsection
