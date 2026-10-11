<?php

namespace App\Services;

use App\Models\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoriaService
{
    public function paginate(): LengthAwarePaginator
    {
        return Categoria::query()
            ->withCount('productos')
            ->orderBy('nombre')->orderBy('id')->paginate(15);
    }

    public function findForEditing(Categoria $categoria): Categoria
    {
        return $categoria;
    }

    /** @param array{nombre: string} $data */
    public function create(array $data): Categoria
    {
        return Categoria::query()->create($data);
    }

    /** @param array{nombre: string} $data */
    public function update(Categoria $categoria, array $data): Categoria
    {
        $categoria->update($data);

        return $categoria;
    }

    public function delete(Categoria $categoria): void
    {
        DB::transaction(function () use ($categoria): void {
            $locked = Categoria::query()->whereKey($categoria->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->productos()->exists()) {
                throw ValidationException::withMessages(['nombre' => 'No se puede eliminar porque tiene productos asociados.']);
            }
            $locked->delete();
        });
    }
}
