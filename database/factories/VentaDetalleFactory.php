<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VentaDetalle>
 */
class VentaDetalleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venta_id' => Venta::factory(), 'producto_id' => Producto::factory(), 'nombre_producto' => 'edge 5W-30 1L', 'marca_producto' => 'Castrol', 'cantidad' => 1, 'tipo_precio' => 'menor', 'precio_unitario' => '30.00', 'subtotal' => '30.00',
        ];
    }
}
