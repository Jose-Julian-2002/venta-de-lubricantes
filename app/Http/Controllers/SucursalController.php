<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSucursalRequest;
use App\Http\Requests\UpdateSucursalRequest;
use App\Models\Sucursal;
use App\Services\SucursalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SucursalController extends Controller
{
    public function index(SucursalService $service): View
    {
        return view('admin.sucursales.index', ['sucursales' => $service->paginate()]);
    }

    public function create(): View
    {
        return view('admin.sucursales.create');
    }

    public function store(StoreSucursalRequest $request, SucursalService $service): RedirectResponse
    {
        $service->create($request->validated());

        return redirect()->route('admin.sucursales.index')->with('status', 'Sucursal creada correctamente.');
    }

    public function edit(Sucursal $sucursal): View
    {
        return view('admin.sucursales.edit', ['sucursal' => $sucursal]);
    }

    public function update(UpdateSucursalRequest $request, Sucursal $sucursal, SucursalService $service): RedirectResponse
    {
        $service->update($sucursal, $request->validated());

        return redirect()->route('admin.sucursales.index')->with('status', 'Sucursal actualizada correctamente.');
    }
}
