<?php

namespace Database\Seeders;

use App\Models\Marca;
use Illuminate\Database\Seeder;

class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Castrol', 'Shell', 'Lubrax'] as $name) {
            Marca::query()->firstOrCreate(['nombre' => $name]);
        }
    }
}
