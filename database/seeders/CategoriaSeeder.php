<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Aceites de auto', 'Refrigerantes', 'Repuestos'] as $name) {
            Categoria::query()->firstOrCreate(['nombre' => $name]);
        }
    }
}
