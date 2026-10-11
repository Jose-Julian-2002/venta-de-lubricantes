<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexProductoRequest;
use App\Models\Producto;
use App\Services\StockService;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(IndexProductoRequest $request, StockService $service): View
    {
        return view('admin.stock.index', ['productos' => $service->inventory($request->validated()), 'filters' => $request->validated(), ...$service->formOptions()]);
    }

    public function kardex(Producto $producto, StockService $service): View
    {
        return view('admin.stock.kardex', ['producto' => $service->findForViewing($producto), 'movimientos' => $service->movements($producto)]);
    }
}
