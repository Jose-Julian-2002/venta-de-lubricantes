<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucursalPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $ids = [];
            foreach (array_keys(config('permissions.catalog')) as $name) {
                if (str_starts_with($name, 'sucursales.')) {
                    $ids[] = Permiso::query()->firstOrCreate(['nombre' => $name])->getKey();
                }
            }
            foreach (Rol::query()->whereIn('nombre', ['Administrador', 'Administrador de pruebas'])->get() as $rol) {
                $rol->permisos()->syncWithoutDetaching($ids);
            }
        });
    }
}
