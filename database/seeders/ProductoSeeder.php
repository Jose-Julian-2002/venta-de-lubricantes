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
            'nombre' => 'Castrol 20w50 1L',
            'categoria_id' => Categoria::query()->where('nombre', 'Aceites de auto')->firstOrFail()->id,
            'marca_id' => Marca::query()->where('nombre', 'Castrol')->firstOrFail()->id,
        ], [
            'precio_compra_referencia' => '0.00',
            'precio_venta_menor' => '0.00',
            'precio_venta_mayor' => '0.00',
            'estado' => false,
        ]);
    }
}
