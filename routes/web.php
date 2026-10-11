<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::middleware(['auth', 'usuario.activo'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/roles', [RolController::class, 'index'])
            ->middleware('can:roles.ver')
            ->name('roles.index');
        Route::get('/roles/create', [RolController::class, 'create'])
            ->middleware('can:roles.crear')
            ->name('roles.create');
        Route::post('/roles', [RolController::class, 'store'])
            ->middleware('can:roles.crear')
            ->name('roles.store');
        Route::get('/roles/{rol}/edit', [RolController::class, 'edit'])
            ->middleware('can:roles.editar')
            ->name('roles.edit');
        Route::put('/roles/{rol}', [RolController::class, 'update'])
            ->middleware('can:roles.editar')
            ->name('roles.update');
        Route::delete('/roles/{rol}', [RolController::class, 'destroy'])
            ->middleware('can:roles.eliminar')
            ->name('roles.destroy');

        Route::get('/usuarios', [UsuarioController::class, 'index'])
            ->middleware('can:usuarios.ver')
            ->name('usuarios.index');
        Route::get('/usuarios/create', [UsuarioController::class, 'create'])
            ->middleware('can:usuarios.crear')
            ->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])
            ->middleware('can:usuarios.crear')
            ->name('usuarios.store');
        Route::get('/usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])
            ->middleware('can:usuarios.editar')
            ->name('usuarios.edit');
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])
            ->middleware('can:usuarios.editar')
            ->name('usuarios.update');
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])
            ->middleware('can:usuarios.eliminar')
            ->name('usuarios.destroy');
    });
});
