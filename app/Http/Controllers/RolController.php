<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyRolRequest;
use App\Http\Requests\StoreRolRequest;
use App\Http\Requests\UpdateRolRequest;
use App\Models\Rol;
use App\Services\RolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RolController extends Controller
{
    public function index(RolService $rolService): View
    {
        return view('admin.roles.index', ['roles' => $rolService->paginate()]);
    }

    public function create(RolService $rolService): View
    {
        return view('admin.roles.create', [
            'permisos' => $rolService->availablePermissions(),
        ]);
    }

    public function store(StoreRolRequest $request, RolService $rolService): RedirectResponse
    {
        $rolService->create($request->validated());

        return redirect()->route('admin.roles.index')->with('status', __('admin.roles.created'));
    }

    public function edit(Rol $rol, RolService $rolService): View
    {
        return view('admin.roles.edit', [
            'rol' => $rolService->findForEditing($rol),
            'permisos' => $rolService->availablePermissions(),
        ]);
    }

    public function update(UpdateRolRequest $request, Rol $rol, RolService $rolService): RedirectResponse
    {
        $rolService->update($rol, $request->validated());

        return redirect()->route('admin.roles.index')->with('status', __('admin.roles.updated'));
    }

    public function destroy(
        DestroyRolRequest $request,
        Rol $rol,
        RolService $rolService,
    ): RedirectResponse {
        $rolService->delete($rol);

        return redirect()->route('admin.roles.index')->with('status', __('admin.roles.deleted'));
    }
}
