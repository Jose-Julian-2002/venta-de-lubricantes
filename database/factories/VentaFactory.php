<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::factory(), 'cliente_id' => Cliente::factory(), 'token' => (string) Str::uuid(), 'tipo_pago' => 'efectivo', 'total' => '30.00', 'estado' => 'completada', 'created_at' => now(),
        ];
    }
}
