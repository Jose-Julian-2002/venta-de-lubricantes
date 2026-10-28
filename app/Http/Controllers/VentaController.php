<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexVentaRequest;
use App\Http\Requests\StoreVentaRequest;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(IndexVentaRequest $request, VentaService $service): View
    {
        return view('admin.ventas.index', [
            'ventas' => $service->paginate($request->validated()),
            'filters' => $request->validated(),
            'sucursales' => $service->availableSucursales(),
        ]);
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
