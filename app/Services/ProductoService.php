<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductoService
{
    /** @param array{buscar?: string|null, categoria_id?: int|string|null, marca_id?: int|string|null} $filters */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Producto::query()
            ->with(['categoria', 'marca'])
            ->when(isset($filters['buscar']) && $filters['buscar'] !== '', fn (Builder $query): Builder => $query->where('nombre', 'like', '%'.$filters['buscar'].'%'))
            ->when($filters['categoria_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('categoria_id', $id))
            ->when($filters['marca_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('marca_id', $id))
            ->orderBy('nombre')->orderBy('id')->paginate(15)->withQueryString();
    }

    /** @return array{categorias: Collection<int, Categoria>, marcas: Collection<int, Marca>} */
    public function formOptions(): array
    {
        return ['categorias' => Categoria::query()->orderBy('nombre')->get(), 'marcas' => Marca::query()->orderBy('nombre')->get()];
    }

    public function findForEditing(Producto $producto): Producto
    {
        return $producto;
    }

    /** @param array{categoria_id: int|string, marca_id: int|string, nombre: string, precio_compra_referencia: string|int|float, precio_venta_menor: string|int|float, precio_venta_mayor: string|int|float, estado: bool|int|string} $data */
    public function create(array $data): Producto
    {
        return Producto::query()->create($data);
    }

    /** @param array{categoria_id: int|string, marca_id: int|string, nombre: string, precio_compra_referencia: string|int|float, precio_venta_menor: string|int|float, precio_venta_mayor: string|int|float, estado: bool|int|string} $data */
    public function update(Producto $producto, array $data): Producto
    {
        $producto->update($data);

        return $producto;
    }

    public function delete(Producto $producto): void
    {
        DB::transaction(function () use ($producto): void {
            $locked = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->stock_actual > 0) {
                throw ValidationException::withMessages(['nombre' => 'No se puede eliminar un producto con stock. Puedes desactivarlo.']);
            }
            $locked->delete();
        });
    }
}
