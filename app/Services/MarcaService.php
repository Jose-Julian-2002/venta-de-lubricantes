<?php

namespace App\Services;

use App\Models\Marca;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarcaService
{
    public function paginate(): LengthAwarePaginator
    {
        return Marca::query()
            ->withCount('productos')
            ->orderBy('nombre')->orderBy('id')->paginate(15);
    }

    public function findForEditing(Marca $marca): Marca
    {
        return $marca;
    }

    /** @param array{nombre: string} $data */
    public function create(array $data): Marca
    {
        return Marca::query()->create($data);
    }

    /** @param array{nombre: string} $data */
    public function update(Marca $marca, array $data): Marca
    {
        $marca->update($data);

        return $marca;
    }

    public function delete(Marca $marca): void
    {
        DB::transaction(function () use ($marca): void {
            $locked = Marca::query()->whereKey($marca->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->productos()->exists()) {
                throw ValidationException::withMessages(['nombre' => 'No se puede eliminar porque tiene productos asociados.']);
            }
            $locked->delete();
        });
    }
}
