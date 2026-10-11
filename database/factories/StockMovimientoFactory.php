<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StockMovimiento> */
class StockMovimientoFactory extends Factory
{
    protected $model = StockMovimiento::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['producto_id' => Producto::factory(), 'usuario_id' => Usuario::factory(), 'compra_detalle_id' => null, 'tipo' => 'entrada', 'cantidad' => 12, 'stock_anterior' => 0, 'stock_nuevo' => 12, 'motivo' => 'compra', 'created_at' => now()];
    }
}
