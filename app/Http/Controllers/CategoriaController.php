<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyCategoriaRequest;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;
use App\Services\CategoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(CategoriaService $service): View
    {
        return view('admin.categorias.index', ['categorias' => $service->paginate()]);
    }

    public function create(CategoriaService $service): View
    {
        return view('admin.categorias.create');
    }

    public function store(StoreCategoriaRequest $request, CategoriaService $service): RedirectResponse
    {
        $service->create($request->validated());

        return redirect()->route('admin.categorias.index')->with('status', 'Registro creado correctamente.');
    }

    public function edit(Categoria $categoria, CategoriaService $service): View
    {
        return view('admin.categorias.edit', ['categoria' => $service->findForEditing($categoria)]);
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria, CategoriaService $service): RedirectResponse
    {
        $service->update($categoria, $request->validated());

        return redirect()->route('admin.categorias.index')->with('status', 'Registro actualizado correctamente.');
    }

    public function destroy(DestroyCategoriaRequest $request, Categoria $categoria, CategoriaService $service): RedirectResponse
    {
        $service->delete($categoria);

        return redirect()->route('admin.categorias.index')->with('status', 'Registro eliminado correctamente.');
    }
}
