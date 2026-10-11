@extends('layouts.admin')

@section('title', 'Roles')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">Roles</h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Define perfiles y los permisos disponibles para cada uno.</p>
            </div>
            @can('roles.crear')
                <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 dark:focus:ring-offset-slate-950">
                    <x-icon name="plus" class="size-[18px]" />
                    Crear rol
                </a>
            @endcan
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/70 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-5 py-3">Nombre</th>
                            <th scope="col" class="px-5 py-3">Usuarios</th>
                            <th scope="col" class="px-5 py-3">Permisos</th>
                            <th scope="col" class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($roles as $rol)
                            <tr>
                                <td class="px-5 py-4 font-medium">{{ $rol->nombre }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $rol->usuarios_count }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $rol->permisos_count }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        @can('roles.editar')
                                            <a href="{{ route('admin.roles.edit', $rol) }}" class="inline-flex items-center gap-1.5 font-medium text-blue-700 hover:text-blue-900 dark:text-blue-300 dark:hover:text-blue-200">
                                                <x-icon name="edit" class="size-4" />
                                                Editar
                                            </a>
                                        @endcan
                                        @can('roles.eliminar')
                                            <form action="{{ route('admin.roles.destroy', $rol) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" @disabled($rol->usuarios_count > 0)
                                                    class="inline-flex items-center gap-1.5 font-medium text-rose-700 hover:text-rose-900 disabled:cursor-not-allowed disabled:text-slate-400 dark:text-rose-300 dark:hover:text-rose-200 dark:disabled:text-slate-600"
                                                    @if ($rol->usuarios_count > 0) title="El rol tiene usuarios asignados" @endif>
                                                    <x-icon name="trash" class="size-4" />
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">Todavía no hay roles.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($roles->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">{{ $roles->links() }}</div>
            @endif
        </div>
    </div>
@endsection
