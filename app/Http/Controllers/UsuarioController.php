<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyUsuarioRequest;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Usuario;
use App\Services\UsuarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(UsuarioService $usuarioService): View
    {
        return view('admin.usuarios.index', ['usuarios' => $usuarioService->paginate()]);
    }

    public function create(UsuarioService $usuarioService): View
    {
        return view('admin.usuarios.create', [
            'roles' => $usuarioService->availableRoles(),
            'sucursales' => $usuarioService->availableSucursales(),
        ]);
    }

    public function store(StoreUsuarioRequest $request, UsuarioService $usuarioService): RedirectResponse
    {
        $usuarioService->create($request->validated());

        return redirect()->route('admin.usuarios.index')->with('status', __('admin.users.created'));
    }

    public function edit(Usuario $usuario, UsuarioService $usuarioService): View
    {
        return view('admin.usuarios.edit', [
            'usuario' => $usuarioService->findForEditing($usuario),
            'roles' => $usuarioService->availableRoles(),
            'sucursales' => $usuarioService->availableSucursales(),
        ]);
    }

    public function update(
        UpdateUsuarioRequest $request,
        Usuario $usuario,
        UsuarioService $usuarioService,
    ): RedirectResponse {
        $usuarioService->update($usuario, $request->validated());

        return redirect()->route('admin.usuarios.index')->with('status', __('admin.users.updated'));
    }

    public function destroy(
        DestroyUsuarioRequest $request,
        Usuario $usuario,
        UsuarioService $usuarioService,
    ): RedirectResponse {
        $usuarioService->delete($usuario);

        return redirect()->route('admin.usuarios.index')->with('status', __('admin.users.deleted'));
    }
}
