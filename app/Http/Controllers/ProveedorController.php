<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyProveedorRequest;
use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Models\Proveedor;
use App\Services\ProveedorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(ProveedorService $service): View
    {
        return view('admin.proveedores.index', ['proveedores' => $service->paginate()]);
    }

    public function create(ProveedorService $service): View
    {
        return view('admin.proveedores.create');
    }

    public function store(StoreProveedorRequest $request, ProveedorService $service): RedirectResponse
    {
        $service->create($request->validated());

        return redirect()->route('admin.proveedores.index')->with('status', 'Registro creado correctamente.');
    }

    public function edit(Proveedor $proveedor, ProveedorService $service): View
    {
        return view('admin.proveedores.edit', ['proveedor' => $service->findForEditing($proveedor)]);
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor, ProveedorService $service): RedirectResponse
    {
        $service->update($proveedor, $request->validated());

        return redirect()->route('admin.proveedores.index')->with('status', 'Registro actualizado correctamente.');
    }

    public function destroy(DestroyProveedorRequest $request, Proveedor $proveedor, ProveedorService $service): RedirectResponse
    {
        $service->delete($proveedor);

        return redirect()->route('admin.proveedores.index')->with('status', 'Registro eliminado correctamente.');
    }
}
