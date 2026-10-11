<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(VentaService $service): View
    {
        return view('admin.ventas.index', ['ventas' => $service->paginate()]);
    }

    public function create(VentaService $service): View
    {
        return view('admin.ventas.create', $service->posOptions());
    }

    public function store(StoreVentaRequest $request, VentaService $service): RedirectResponse
    {
        $venta = $service->create($request->validated(), $request->user());

        return redirect()->route('admin.ventas.show', $venta)->with('status', 'Venta completada y stock actualizado correctamente.');
    }

    public function show(Venta $venta, VentaService $service): View
    {
        return view('admin.ventas.show', ['venta' => $service->findForViewing($venta)]);
    }
}
