<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Services\CompraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function index(CompraService $service): View
    {
        return view('admin.compras.index', ['compras' => $service->paginate()]);
    }

    public function create(CompraService $service): View
    {
        return view('admin.compras.create', $service->formOptions());
    }

    public function store(StoreCompraRequest $request, CompraService $service): RedirectResponse
    {
        $compra = $service->create($request->validated(), $request->user());

        return ($request->user()->can('compras.ver')
            ? redirect()->route('admin.compras.show', $compra)
            : redirect()->route('admin.compras.create'))
            ->with('status', 'Compra registrada y stock actualizado correctamente.');
    }

    public function show(Compra $compra, CompraService $service): View
    {
        return view('admin.compras.show', ['compra' => $service->findForViewing($compra)]);
    }
}
