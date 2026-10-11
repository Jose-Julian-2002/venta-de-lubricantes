<?php

namespace App\Models;

use Database\Factories\CompraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    /** @use HasFactory<CompraFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'compras';

    protected $fillable = ['proveedor_id', 'usuario_id', 'total', 'fecha_compra'];

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'fecha_compra' => 'datetime'];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class, 'compra_id');
    }
}
