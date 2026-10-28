<?php

namespace App\Services;

use App\Models\Sucursal;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

class SucursalContext
{
    public function id(): int
    {
        $usuario = Auth::user();

        if (! $usuario instanceof Usuario) {
            return Sucursal::PRINCIPAL_ID;
        }

        return $this->forUsuario($usuario);
    }

    public function forUsuario(Usuario $usuario): int
    {
        if (Auth::id() === $usuario->getKey() && $usuario->hasPermission('sucursales.ver') && request()->hasSession()) {
            return (int) request()->session()->get('sucursal_id', $usuario->sucursal_id);
        }

        return (int) $usuario->sucursal_id;
    }

    public function assertCurrent(int $sucursalId): void
    {
        abort_unless($sucursalId === $this->id(), 404);
    }

    public function assertActive(int $sucursalId): void
    {
        abort_unless(Sucursal::query()->whereKey($sucursalId)->where('estado', true)->exists(), 403, 'La sucursal está inactiva.');
    }
}
