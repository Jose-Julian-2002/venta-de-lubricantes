<?php

namespace App\Http\Controllers;

use App\Http\Requests\SwitchSucursalRequest;
use Illuminate\Http\RedirectResponse;

class SucursalActualController extends Controller
{
    public function store(SwitchSucursalRequest $request): RedirectResponse
    {
        $request->session()->put('sucursal_id', (int) $request->validated('sucursal_id'));

        return redirect()->route('admin.dashboard')->with('status', 'Sucursal seleccionada. Las operaciones y consultas corresponden a esta sucursal.');
    }
}
