<?php

namespace App\Providers;

use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (array_keys(config('permissions.catalog', [])) as $permission) {
            Gate::define(
                $permission,
                fn (Usuario $usuario): bool => $usuario->hasPermission($permission),
            );
        }

        Gate::define('ventas.ticket', fn (Usuario $usuario, Venta $venta): bool => $usuario->hasPermission('ventas.ver') ||
            ($usuario->hasPermission('ventas.crear') && $venta->usuario_id === $usuario->getKey())
        );

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower($request->string('email')->trim()->toString());
            $key = Str::transliterate($email.'|'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });
    }
}
