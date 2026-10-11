<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
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

        Route::resource('categorias', CategoriaController::class)
            ->except('show')->parameters(['categorias' => 'categoria'])
            ->middlewareFor('index', 'can:categorias.ver')
            ->middlewareFor(['create', 'store'], 'can:categorias.crear')
            ->middlewareFor(['edit', 'update'], 'can:categorias.editar')
            ->middlewareFor('destroy', 'can:categorias.eliminar');
        Route::resource('marcas', MarcaController::class)
            ->except('show')->parameters(['marcas' => 'marca'])
            ->middlewareFor('index', 'can:marcas.ver')
            ->middlewareFor(['create', 'store'], 'can:marcas.crear')
            ->middlewareFor(['edit', 'update'], 'can:marcas.editar')
            ->middlewareFor('destroy', 'can:marcas.eliminar');
        Route::resource('productos', ProductoController::class)
            ->except('show')->parameters(['productos' => 'producto'])
            ->middlewareFor('index', 'can:productos.ver')
            ->middlewareFor(['create', 'store'], 'can:productos.crear')
            ->middlewareFor(['edit', 'update'], 'can:productos.editar')
            ->middlewareFor('destroy', 'can:productos.eliminar');

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
