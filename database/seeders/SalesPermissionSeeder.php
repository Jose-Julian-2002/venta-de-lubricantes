<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $ids = [];
            foreach (['ventas.ver', 'ventas.crear', 'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar'] as $name) {
                $ids[] = Permiso::query()->firstOrCreate(['nombre' => $name])->getKey();
            }
            $role = Rol::query()->where('nombre', 'Administrador')->first();
            if ($role !== null) {
                $role->permisos()->syncWithoutDetaching($ids);
            }
            Cliente::query()->firstOrCreate(['nombre' => 'Cliente General']);
        });
    }
}
