<?php

namespace App\Models;

use Database\Factories\VentaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    /** @use HasFactory<VentaFactory> */
    use HasFactory;

    protected $table = 'ventas';

    public $timestamps = false;

    protected $fillable = ['sucursal_id', 'usuario_id', 'cliente_id', 'token', 'tipo_pago', 'total', 'estado', 'created_at'];

    protected $attributes = ['sucursal_id' => Sucursal::PRINCIPAL_ID];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'created_at' => 'datetime'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class, 'venta_id');
    }
}
