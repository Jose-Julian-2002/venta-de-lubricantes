<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Services\ClienteService;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    public function store(StoreClienteRequest $request, ClienteService $service): JsonResponse
    {
        $cliente = $service->create($request->validated());

        return response()->json(['cliente' => $cliente->only(['id', 'nombre', 'nit_ci', 'telefono'])], 201);
    }
}
