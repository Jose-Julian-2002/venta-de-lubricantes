<?php

namespace App\Models;

use Database\Factories\VentaDetalleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VentaDetalle extends Model
{
    /** @use HasFactory<VentaDetalleFactory> */
    use HasFactory;

    protected $table = 'venta_detalles';

    public $timestamps = false;

    protected $fillable = ['venta_id', 'producto_id', 'nombre_producto', 'marca_producto', 'cantidad', 'tipo_precio', 'precio_unitario', 'subtotal'];

    protected function casts(): array
    {
        return ['cantidad' => 'integer', 'precio_unitario' => 'decimal:2', 'subtotal' => 'decimal:2'];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class, 'venta_detalle_id');
    }
}
