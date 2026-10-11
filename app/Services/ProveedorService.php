<?php

namespace App\Services;

use App\Models\Proveedor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProveedorService
{
    public function paginate(): LengthAwarePaginator
    {
        return Proveedor::query()->withCount('compras')->orderBy('nombre_empresa')->orderBy('id')->paginate(15);
    }

    public function findForEditing(Proveedor $proveedor): Proveedor
    {
        return $proveedor;
    }

    /** @param array{nombre_empresa: string, nombre?: string|null, telefono?: string|null, nit_ci?: string|null, direccion?: string|null} $data */
    public function create(array $data): Proveedor
    {
        return Proveedor::query()->create($data);
    }

    /** @param array{nombre_empresa: string, nombre?: string|null, telefono?: string|null, nit_ci?: string|null, direccion?: string|null} $data */
    public function update(Proveedor $proveedor, array $data): Proveedor
    {
        $proveedor->update($data);

        return $proveedor;
    }

    public function delete(Proveedor $proveedor): void
    {
        DB::transaction(function () use ($proveedor): void {
            $lockedProveedor = Proveedor::query()->whereKey($proveedor->getKey())->lockForUpdate()->firstOrFail();
            if ($lockedProveedor->compras()->exists()) {
                throw ValidationException::withMessages(['nombre_empresa' => 'No se puede eliminar el proveedor porque tiene compras registradas.']);
            }
            $lockedProveedor->delete();
        });
    }
}
