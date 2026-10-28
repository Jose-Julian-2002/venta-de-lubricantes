<?php

namespace App\Http\Middleware;

use App\Services\SucursalContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSucursalActiva
{
    public function __construct(private SucursalContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->context->assertActive($this->context->id());

        return $next($request);
    }
}
