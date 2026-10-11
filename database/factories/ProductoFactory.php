<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Producto> */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(), 'marca_id' => Marca::factory(),
            'nombre' => fake()->words(3, true), 'stock_actual' => 0,
            'precio_compra_referencia' => '20.00', 'precio_venta_menor' => '30.00',
            'precio_venta_mayor' => '25.00', 'estado' => true, ];
    }
}
