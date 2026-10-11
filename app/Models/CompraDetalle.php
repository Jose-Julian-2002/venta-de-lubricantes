<?php

namespace App\Models;

use Database\Factories\CompraDetalleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompraDetalle extends Model
{
    /** @use HasFactory<CompraDetalleFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'compra_detalles';

    protected $fillable = ['compra_id', 'producto_id', 'cantidad_cajas', 'unidades_por_caja', 'cantidad_total_unidades', 'precio_compra_caja', 'costo_unitario', 'subtotal'];

    protected function casts(): array
    {
        return ['cantidad_cajas' => 'integer', 'unidades_por_caja' => 'integer', 'cantidad_total_unidades' => 'integer', 'precio_compra_caja' => 'decimal:2', 'costo_unitario' => 'decimal:2', 'subtotal' => 'decimal:2'];
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function stockMovimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class, 'compra_detalle_id');
    }
}
