<?php

namespace App\Services;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UsuarioService
{
    public function paginate(): LengthAwarePaginator
    {
        return Usuario::query()
            ->with('rol')
            ->orderBy('nombre')
            ->paginate(15);
    }

    /**
     * @return Collection<int, Rol>
     */
    public function availableRoles(): Collection
    {
        return Rol::query()->orderBy('nombre')->get();
    }

    /**
     * @param  array{rol_id: int|string, nombre: string, email: string, password: string, estado: bool|string, password_confirmation?: string}  $data
     */
    public function create(array $data): Usuario
    {
        unset($data['password_confirmation']);

        return DB::transaction(fn (): Usuario => Usuario::query()->create($data));
    }

    /**
     * @param  array{rol_id: int|string, nombre: string, email: string, password?: string|null, estado: bool|string, password_confirmation?: string}  $data
     */
    public function update(Usuario $usuario, array $data): Usuario
    {
        unset($data['password_confirmation']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return DB::transaction(function () use ($usuario, $data): Usuario {
            $usuario->update($data);

            return $usuario->refresh();
        });
    }

    public function delete(Usuario $usuario): void
    {
        if ((int) Auth::id() === (int) $usuario->getKey()) {
            throw ValidationException::withMessages([
                'usuario' => __('admin.users.cannot_delete_self'),
            ]);
        }

        DB::transaction(function () use ($usuario): void {
            $locked = Usuario::query()->whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->compras()->exists() || $locked->stockMovimientos()->exists()) {
                throw ValidationException::withMessages(['usuario' => 'No se puede eliminar un usuario con compras o movimientos registrados. Puedes desactivarlo.']);
            }
            $locked->delete();
        });
    }
}
