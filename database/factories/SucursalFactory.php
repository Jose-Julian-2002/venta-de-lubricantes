<?php

namespace Database\Factories;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Sucursal> */
class SucursalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->company(),
            'direccion' => fake()->streetAddress(),
            'telefono' => fake()->numerify('########'),
            'estado' => true,
        ];
    }
}
