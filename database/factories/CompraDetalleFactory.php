<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CompraDetalle> */
class CompraDetalleFactory extends Factory
{
    protected $model = CompraDetalle::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['compra_id' => Compra::factory(), 'producto_id' => Producto::factory(), 'cantidad_cajas' => 1, 'unidades_por_caja' => 12, 'cantidad_total_unidades' => 12, 'precio_compra_caja' => '120.00', 'costo_unitario' => '10.00', 'subtotal' => '120.00'];
    }
}
