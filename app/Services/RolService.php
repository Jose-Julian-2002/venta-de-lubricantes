<?php

namespace App\Services;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RolService
{
    public function paginate(): LengthAwarePaginator
    {
        return Rol::query()
            ->withCount(['usuarios', 'permisos'])
            ->orderBy('nombre')
            ->paginate(15);
    }

    /**
     * @return Collection<int, Permiso>
     */
    public function availablePermissions(): Collection
    {
        return Permiso::query()
            ->when(! auth()->user()?->can('sucursales.ver'), fn ($query) => $query->where('nombre', 'not like', 'sucursales.%'))
            ->orderBy('nombre')->get();
    }

    public function findForEditing(Rol $rol): Rol
    {
        abort_if(! auth()->user()?->can('sucursales.ver') && $rol->permisos()->where('nombre', 'like', 'sucursales.%')->exists(), 404);

        return $rol->load('permisos');
    }

    /** @return array<string, Collection<int, Permiso>> */
    public function groupedPermissions(): array
    {
        $permissions = $this->availablePermissions()->keyBy('nombre');
        $groups = [];
        foreach (config('permissions.sections') as $section => $modules) {
            $group = new Collection;
            foreach ($modules as $module) {
                foreach (array_keys(config('permissions.catalog')) as $name) {
                    if (str_starts_with($name, $module.'.') && $permissions->has($name)) {
                        $group->push($permissions->pull($name));
                    }
                }
            }
            if ($group->isNotEmpty()) {
                $groups[$section] = $group;
            }
        }
        if ($permissions->isNotEmpty()) {
            $groups['Otros permisos'] = $permissions->values();
        }

        return $groups;
    }

    /**
     * @param  array{nombre: string, permisos?: array<int, int|string>|null}  $data
     */
    public function create(array $data): Rol
    {
        return DB::transaction(function () use ($data): Rol {
            $rol = Rol::query()->create(['nombre' => $data['nombre']]);
            $rol->permisos()->sync($data['permisos'] ?? []);

            return $rol;
        });
    }

    /**
     * @param  array{nombre: string, permisos?: array<int, int|string>|null}  $data
     */
    public function update(Rol $rol, array $data): Rol
    {
        $this->findForEditing($rol);

        return DB::transaction(function () use ($rol, $data): Rol {
            $rol->update(['nombre' => $data['nombre']]);
            $rol->permisos()->sync($data['permisos'] ?? []);

            return $rol;
        });
    }

    public function delete(Rol $rol): void
    {
        $this->findForEditing($rol);
        DB::transaction(function () use ($rol): void {
            $lockedRol = Rol::query()->whereKey($rol->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedRol->usuarios()->exists()) {
                throw ValidationException::withMessages([
                    'nombre' => __('admin.roles.in_use'),
                ]);
            }

            $lockedRol->delete();
        });
    }
}
