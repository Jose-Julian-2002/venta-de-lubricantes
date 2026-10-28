<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'productos';

    protected $fillable = ['categoria_id', 'marca_id', 'nombre', 'precio_venta_menor', 'precio_venta_mayor', 'estado', 'foto_path', 'foto_miniatura_path'];

    protected static function booted(): void
    {
        static::created(function (Producto $producto): void {
            if ($producto->stock_actual > 0) {
                SucursalProducto::query()->create([
                    'sucursal_id' => Sucursal::PRINCIPAL_ID,
                    'producto_id' => $producto->getKey(),
                    'stock_actual' => $producto->stock_actual,
                ]);
            }
        });
    }

    public function fotoUrl(): ?string
    {
        return $this->foto_path === null ? null : Storage::disk('public')->url($this->foto_path);
    }

    public function fotoMiniaturaUrl(): ?string
    {
        return $this->foto_miniatura_path === null ? null : Storage::disk('public')->url($this->foto_miniatura_path);
    }

    protected function casts(): array
    {
        return ['stock_actual' => 'integer', 'estado' => 'boolean', 'precio_venta_menor' => 'decimal:2', 'precio_venta_mayor' => 'decimal:2'];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function ultimaCompraDetalle(): HasOne
    {
        return $this->hasOne(CompraDetalle::class, 'producto_id')->latestOfMany();
    }

    public function compraDetalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class, 'producto_id');
    }

    public function ventaDetalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class, 'producto_id');
    }

    public function stockMovimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class, 'producto_id');
    }
}
