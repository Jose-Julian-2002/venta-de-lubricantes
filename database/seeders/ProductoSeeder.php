<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([CategoriaSeeder::class, MarcaSeeder::class]);
        Producto::query()->firstOrCreate([
            'nombre' => 'edge 5W-30 1L',
            'categoria_id' => Categoria::query()->where('nombre', 'Aceites de auto')->firstOrFail()->id,
            'marca_id' => Marca::query()->where('nombre', 'Castrol')->firstOrFail()->id,
        ], [
            'precio_venta_menor' => null,
            'precio_venta_mayor' => null,
            'estado' => false,
        ]);
    }
}
