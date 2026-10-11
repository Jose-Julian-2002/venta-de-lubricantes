<?php

namespace App\Models;

use Database\Factories\StockMovimientoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovimiento extends Model
{
    /** @use HasFactory<StockMovimientoFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'stock_movimientos';

    protected $fillable = ['producto_id', 'usuario_id', 'compra_detalle_id', 'venta_detalle_id', 'tipo', 'cantidad', 'stock_anterior', 'stock_nuevo', 'motivo', 'created_at'];

    protected function casts(): array
    {
        return ['cantidad' => 'integer', 'stock_anterior' => 'integer', 'stock_nuevo' => 'integer', 'created_at' => 'datetime'];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function ventaDetalle(): BelongsTo
    {
        return $this->belongsTo(VentaDetalle::class, 'venta_detalle_id');
    }

    public function compraDetalle(): BelongsTo
    {
        return $this->belongsTo(CompraDetalle::class, 'compra_detalle_id');
    }
}
