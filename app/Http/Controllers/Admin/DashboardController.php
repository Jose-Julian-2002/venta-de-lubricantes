<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardRequest;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service) {}

    public function __invoke(DashboardRequest $request): View
    {
        return view('admin.dashboard', [
            'resumen' => $request->user()->can('ventas.ver')
                ? $this->service->resumen($request->validated('mes'))
                : null,
        ]);
    }
}
