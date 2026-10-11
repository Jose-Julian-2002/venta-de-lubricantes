<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyMarcaRequest;
use App\Http\Requests\StoreMarcaRequest;
use App\Http\Requests\UpdateMarcaRequest;
use App\Models\Marca;
use App\Services\MarcaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MarcaController extends Controller
{
    public function index(MarcaService $service): View
    {
        return view('admin.marcas.index', ['marcas' => $service->paginate()]);
    }

    public function create(MarcaService $service): View
    {
        return view('admin.marcas.create');
    }

    public function store(StoreMarcaRequest $request, MarcaService $service): RedirectResponse
    {
        $service->create($request->validated());

        return redirect()->route('admin.marcas.index')->with('status', 'Registro creado correctamente.');
    }

    public function edit(Marca $marca, MarcaService $service): View
    {
        return view('admin.marcas.edit', ['marca' => $service->findForEditing($marca)]);
    }

    public function update(UpdateMarcaRequest $request, Marca $marca, MarcaService $service): RedirectResponse
    {
        $service->update($marca, $request->validated());

        return redirect()->route('admin.marcas.index')->with('status', 'Registro actualizado correctamente.');
    }

    public function destroy(DestroyMarcaRequest $request, Marca $marca, MarcaService $service): RedirectResponse
    {
        $service->delete($marca);

        return redirect()->route('admin.marcas.index')->with('status', 'Registro eliminado correctamente.');
    }
}
