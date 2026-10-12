<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyClienteRequest;
use App\Http\Requests\FilterClienteRequest;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Services\ClienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(FilterClienteRequest $request, ClienteService $service): View
    {
        return view('admin.clientes.index', ['clientes' => $service->paginate($request->validated())]);
    }

    public function create(): View
    {
        return view('admin.clientes.create');
    }

    public function store(StoreClienteRequest $request, ClienteService $service): JsonResponse|RedirectResponse
    {
        $cliente = $service->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['cliente' => $cliente->only(['id', 'nombre', 'nit_ci', 'telefono'])], 201);
        }

        $route = $request->user()->can('clientes.ver')
            ? 'admin.clientes.index'
            : ($request->user()->can('clientes.crear') ? 'admin.clientes.create' : 'admin.ventas.create');

        return redirect()->route($route)->with('status', 'Cliente creado correctamente.');
    }

    public function edit(Cliente $cliente, ClienteService $service): View
    {
        return view('admin.clientes.edit', ['cliente' => $service->findForEditing($cliente)]);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente, ClienteService $service): RedirectResponse
    {
        $service->update($cliente, $request->validated());

        return redirect()->route($request->user()->can('clientes.ver') ? 'admin.clientes.index' : 'admin.clientes.edit', $request->user()->can('clientes.ver') ? [] : ['cliente' => $cliente])->with('status', 'Cliente actualizado correctamente.');
    }

    public function destroy(DestroyClienteRequest $request, Cliente $cliente, ClienteService $service): RedirectResponse
    {
        $service->delete($cliente);

        return redirect()->route($request->user()->can('clientes.ver') ? 'admin.clientes.index' : 'admin.dashboard')->with('status', 'Cliente eliminado correctamente.');
    }
}
