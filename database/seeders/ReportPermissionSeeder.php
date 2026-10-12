<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $permission = Permiso::query()->firstOrCreate(['nombre' => 'reportes.ver']);
            $role = Rol::query()->where('nombre', 'Administrador')->first();
            if ($role !== null) {
                $role->permisos()->syncWithoutDetaching([$permission->getKey()]);
            }
        });
    }
}
