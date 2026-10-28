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
use Throwable;

class ProductoService
{
    public function __construct(private InventarioSucursalService $inventario, private ProductoFotoService $fotos) {}

    /** @param array{buscar?: string|null, categoria_id?: int|string|null, marca_id?: int|string|null} $filters */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->inventario->productos()
            ->with(['categoria', 'marca'])
            ->when(isset($filters['buscar']) && $filters['buscar'] !== '', fn (Builder $query): Builder => $query->where('nombre', 'like', '%'.$filters['buscar'].'%'))
            ->when($filters['categoria_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('categoria_id', $id))
            ->when($filters['marca_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('marca_id', $id))
            ->orderBy('nombre')->orderBy('productos.id')->paginate(15)->withQueryString();
    }

    /** @return array{categorias: Collection<int, Categoria>, marcas: Collection<int, Marca>} */
    public function formOptions(): array
    {
        return ['categorias' => Categoria::query()->orderBy('nombre')->get(), 'marcas' => Marca::query()->orderBy('nombre')->get()];
    }

    public function findForEditing(Producto $producto): Producto
    {
        return $this->inventario->productos()->where('productos.id', $producto->getKey())->firstOrFail();
    }

    /** @param array{categoria_id: int|string, marca_id: int|string, nombre: string, precio_venta_menor?: string|int|float|null, precio_venta_mayor?: string|int|float|null, estado: bool|int|string} $data */
    public function create(array $data): Producto
    {
        $paths = isset($data['foto']) ? $this->fotos->store($data['foto']) : [];
        unset($data['foto'], $data['quitar_foto']);
        try {
            return DB::transaction(fn (): Producto => Producto::query()->create([...$data, ...$paths]));
        } catch (Throwable $exception) {
            $this->fotos->delete($paths);
            throw $exception;
        }
    }

    /** @param array{categoria_id: int|string, marca_id: int|string, nombre: string, precio_venta_menor?: string|int|float|null, precio_venta_mayor?: string|int|float|null, estado: bool|int|string} $data */
    public function update(Producto $producto, array $data): Producto
    {
        $paths = isset($data['foto']) ? $this->fotos->store($data['foto']) : [];
        $replace = $paths !== [] || (bool) ($data['quitar_foto'] ?? false);
        unset($data['foto'], $data['quitar_foto']);
        $oldPaths = [];
        try {
            $updated = DB::transaction(function () use ($producto, $data, $paths, $replace, &$oldPaths): Producto {
                $locked = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
                if ($replace) {
                    $oldPaths = ['foto_path' => $locked->foto_path, 'foto_miniatura_path' => $locked->foto_miniatura_path];
                    $data = [...$data, 'foto_path' => $paths['foto_path'] ?? null, 'foto_miniatura_path' => $paths['foto_miniatura_path'] ?? null];
                }
                $locked->update($data);

                return $locked;
            });
        } catch (Throwable $exception) {
            $this->fotos->delete($paths);
            throw $exception;
        }
        if ($replace) {
            $this->fotos->delete($oldPaths);
        }

        return $updated;
    }

    public function delete(Producto $producto): void
    {
        $paths = DB::transaction(function () use ($producto): array {
            $locked = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->stock_actual > 0) {
                throw ValidationException::withMessages(['nombre' => 'No se puede eliminar un producto con stock. Puedes desactivarlo.']);
            }
            if ($locked->compraDetalles()->exists() || $locked->ventaDetalles()->exists() || $locked->stockMovimientos()->exists()) {
                throw ValidationException::withMessages(['nombre' => 'No se puede eliminar un producto con historial de inventario. Puedes desactivarlo.']);
            }
            $locked->delete();

            return ['foto_path' => $locked->foto_path, 'foto_miniatura_path' => $locked->foto_miniatura_path];
        });
        $this->fotos->delete($paths);
    }
}
