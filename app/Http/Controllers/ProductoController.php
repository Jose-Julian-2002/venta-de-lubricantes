<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyProductoRequest;
use App\Http\Requests\IndexProductoRequest;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(IndexProductoRequest $request, ProductoService $service): View
    {
        return view('admin.productos.index', ['productos' => $service->paginate($request->validated()), 'filters' => $request->validated(), ...$service->formOptions()]);
    }

    public function create(ProductoService $service): View
    {
        return view('admin.productos.create', $service->formOptions());
    }

    public function store(StoreProductoRequest $request, ProductoService $service): RedirectResponse
    {
        $service->create($request->validated());

        return redirect()->route('admin.productos.index')->with('status', 'Registro creado correctamente.');
    }

    public function edit(Producto $producto, ProductoService $service): View
    {
        return view('admin.productos.edit', ['producto' => $service->findForEditing($producto), ...$service->formOptions()]);
    }

    public function update(UpdateProductoRequest $request, Producto $producto, ProductoService $service): RedirectResponse
    {
        $service->update($producto, $request->validated());

        return redirect()->route('admin.productos.index')->with('status', 'Registro actualizado correctamente.');
    }

    public function destroy(DestroyProductoRequest $request, Producto $producto, ProductoService $service): RedirectResponse
    {
        $service->delete($producto);

        return redirect()->route('admin.productos.index')->with('status', 'Registro eliminado correctamente.');
    }
}
