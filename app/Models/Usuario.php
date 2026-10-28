<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $table = 'usuarios';

    protected $fillable = ['rol_id', 'sucursal_id', 'nombre', 'email', 'password', 'estado'];

    protected $attributes = ['sucursal_id' => Sucursal::PRINCIPAL_ID];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'estado' => 'boolean',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function hasPermission(string $permission): bool
    {
        return $this->rol->permisos->contains('nombre', $permission);
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'usuario_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    public function stockMovimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class, 'usuario_id');
    }
}
