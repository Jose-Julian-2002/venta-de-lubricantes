<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClienteService
{
    /** @param array{buscar?: string|null} $filters */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Cliente::query()->withCount('ventas')
            ->when(isset($filters['buscar']) && $filters['buscar'] !== '', function (Builder $query) use ($filters): void {
                $buscar = $filters['buscar'];
                $query->where(function (Builder $query) use ($buscar): void {
                    $query->where('nombre', 'like', '%'.$buscar.'%')
                        ->orWhere('nit_ci', 'like', '%'.$buscar.'%')
                        ->orWhere('telefono', 'like', '%'.$buscar.'%');
                });
            })->orderBy('nombre')->orderBy('id')->paginate(15)->withQueryString();
    }

    public function findForEditing(Cliente $cliente): Cliente
    {
        return $cliente;
    }

    /** @param array{nombre: string, nit_ci?: string|null, telefono?: string|null} $data */
    public function create(array $data): Cliente
    {
        return Cliente::query()->create($data);
    }

    /** @param array{nombre: string, nit_ci?: string|null, telefono?: string|null} $data */
    public function update(Cliente $cliente, array $data): Cliente
    {
        return DB::transaction(function () use ($cliente, $data): Cliente {
            $lockedCliente = Cliente::query()->whereKey($cliente->getKey())->lockForUpdate()->firstOrFail();
            if ($lockedCliente->nombre === 'Cliente General' && $data['nombre'] !== 'Cliente General') {
                throw ValidationException::withMessages(['nombre' => 'El nombre de Cliente General no se puede modificar.']);
            }
            $lockedCliente->update($data);

            return $lockedCliente;
        }, 3);
    }

    public function delete(Cliente $cliente): void
    {
        DB::transaction(function () use ($cliente): void {
            $lockedCliente = Cliente::query()->whereKey($cliente->getKey())->lockForUpdate()->firstOrFail();
            if ($lockedCliente->nombre === 'Cliente General') {
                throw ValidationException::withMessages(['nombre' => 'Cliente General es el cliente predeterminado y no se puede eliminar.']);
            }
            if ($lockedCliente->ventas()->exists()) {
                throw ValidationException::withMessages(['nombre' => 'No se puede eliminar el cliente porque tiene ventas registradas.']);
            }
            $lockedCliente->delete();
        }, 3);
    }
}
