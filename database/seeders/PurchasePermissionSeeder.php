<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchasePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $ids = [];
            foreach (array_keys(config('permissions.catalog')) as $name) {
                if (str_starts_with($name, 'proveedores.') || str_starts_with($name, 'compras.') || str_starts_with($name, 'stock.')) {
                    $ids[] = Permiso::query()->firstOrCreate(['nombre' => $name])->getKey();
                }
            }
            $adminRole = Rol::query()->where('nombre', 'Administrador')->first();
            if ($adminRole !== null) {
                $adminRole->permisos()->syncWithoutDetaching($ids);
            }
        });
    }
}
