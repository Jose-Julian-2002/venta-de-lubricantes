<?php

namespace App\Models;

use Database\Factories\SucursalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    /** @use HasFactory<SucursalFactory> */
    use HasFactory;

    public const PRINCIPAL_ID = 1;

    public $timestamps = false;

    protected $table = 'sucursales';

    protected $fillable = ['nombre', 'direccion', 'telefono', 'estado'];

    protected function casts(): array
    {
        return ['estado' => 'boolean'];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'sucursal_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'sucursal_id');
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'sucursal_id');
    }

    public function inventario(): HasMany
    {
        return $this->hasMany(SucursalProducto::class, 'sucursal_id');
    }
}
