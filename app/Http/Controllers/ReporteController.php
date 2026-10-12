<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterReporteRequest;
use App\Services\ReporteService;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(FilterReporteRequest $request, ReporteService $service): View
    {
        return view('admin.reportes.index', $service->report($request->validated()));
    }
}
