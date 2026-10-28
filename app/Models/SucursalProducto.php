<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SucursalProducto extends Model
{
    public $timestamps = false;

    protected $table = 'sucursal_productos';

    protected $fillable = ['sucursal_id', 'producto_id', 'stock_actual'];

    protected function casts(): array
    {
        return ['stock_actual' => 'integer'];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
