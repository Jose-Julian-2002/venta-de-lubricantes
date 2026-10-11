<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'productos';

    protected $fillable = ['categoria_id', 'marca_id', 'nombre', 'precio_venta_menor', 'precio_venta_mayor', 'estado'];

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

    public function compraDetalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class, 'producto_id');
    }

    public function stockMovimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class, 'producto_id');
    }
}
