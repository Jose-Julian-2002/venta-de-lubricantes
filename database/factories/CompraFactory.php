<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Compra> */
class CompraFactory extends Factory
{
    protected $model = Compra::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['proveedor_id' => Proveedor::factory(), 'usuario_id' => Usuario::factory(), 'total' => '120.00', 'fecha_compra' => now()];
    }
}
