<?php

namespace App\Services;

use App\Models\Sucursal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SucursalService
{
    public function paginate(): LengthAwarePaginator
    {
        return Sucursal::query()->withCount('usuarios')
            ->withSum('inventario as unidades_stock', 'stock_actual')
            ->withSum(['ventas as ventas_total' => fn ($query) => $query->where('estado', 'completada')], 'total')
            ->withSum('compras as compras_total', 'total')
            ->orderBy('nombre')->orderBy('id')->paginate(15);
    }

    /** @param array{nombre: string, direccion?: string|null, telefono?: string|null, estado: bool|int|string} $data */
    public function create(array $data): Sucursal
    {
        return Sucursal::query()->create($data);
    }

    /** @param array{nombre: string, direccion?: string|null, telefono?: string|null, estado: bool|int|string} $data */
    public function update(Sucursal $sucursal, array $data): void
    {
        DB::transaction(function () use ($sucursal, $data): void {
            $locked = Sucursal::query()->whereKey($sucursal->getKey())->lockForUpdate()->firstOrFail();
            if (! (bool) $data['estado'] && $locked->usuarios()->where('estado', true)->exists()) {
                throw ValidationException::withMessages(['estado' => 'Desactiva o reasigna primero los usuarios activos de esta sucursal.']);
            }
            $locked->update($data);
        });
    }
}
