<?php

namespace App\Services;

use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UsuarioService
{
    public function __construct(private SucursalContext $context) {}

    public function paginate(): LengthAwarePaginator
    {
        return Usuario::query()
            ->where('sucursal_id', $this->context->id())
            ->when(! Auth::user()?->can('sucursales.ver'), fn ($query) => $query->whereDoesntHave('rol.permisos', fn ($permisos) => $permisos->where('nombre', 'like', 'sucursales.%')))
            ->with(['rol', 'sucursal'])
            ->orderBy('nombre')
            ->paginate(15);
    }

    /**
     * @return Collection<int, Rol>
     */
    public function availableRoles(): Collection
    {
        return Rol::query()
            ->when(! Auth::user()?->can('sucursales.ver'), fn ($query) => $query->whereDoesntHave('permisos', fn ($permisos) => $permisos->where('nombre', 'like', 'sucursales.%')))
            ->orderBy('nombre')->get();
    }

    public function findForEditing(Usuario $usuario): Usuario
    {
        $this->context->assertCurrent((int) $usuario->sucursal_id);
        abort_if(! Auth::user()?->can('sucursales.ver') && $usuario->rol->permisos()->where('nombre', 'like', 'sucursales.%')->exists(), 404);

        return $usuario;
    }

    public function availableSucursales(): Collection
    {
        return Sucursal::query()->where('estado', true)
            ->when(! Auth::user()?->can('sucursales.ver'), fn ($query) => $query->whereKey($this->context->id()))
            ->orderBy('nombre')->get();
    }

    /**
     * @param  array{rol_id: int|string, nombre: string, email: string, password: string, estado: bool|string, password_confirmation?: string}  $data
     */
    public function create(array $data): Usuario
    {
        unset($data['password_confirmation']);

        $data['sucursal_id'] ??= $this->context->id();

        return DB::transaction(fn (): Usuario => Usuario::query()->create($data));
    }

    /**
     * @param  array{rol_id: int|string, nombre: string, email: string, password?: string|null, estado: bool|string, password_confirmation?: string}  $data
     */
    public function update(Usuario $usuario, array $data): Usuario
    {
        $this->findForEditing($usuario);
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
        $this->findForEditing($usuario);
        if ((int) Auth::id() === (int) $usuario->getKey()) {
            throw ValidationException::withMessages([
                'usuario' => __('admin.users.cannot_delete_self'),
            ]);
        }

        DB::transaction(function () use ($usuario): void {
            $locked = Usuario::query()->whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->compras()->exists() || $locked->ventas()->exists() || $locked->stockMovimientos()->exists()) {
                throw ValidationException::withMessages(['usuario' => 'No se puede eliminar un usuario con compras, ventas o movimientos registrados. Puedes desactivarlo.']);
            }
            $locked->delete();
        });
    }
}
