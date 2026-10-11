<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Validator::make(config('admin'), [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:12'],
        ])->validate();

        DB::transaction(function () use ($admin): void {
            $permissionIds = collect(array_keys(config('permissions.catalog')))
                ->map(fn (string $name): int => Permiso::query()
                    ->firstOrCreate(['nombre' => $name])
                    ->getKey())
                ->all();

            $adminRole = Rol::query()->firstOrCreate(['nombre' => 'Administrador']);
            $adminRole->permisos()->syncWithoutDetaching($permissionIds);

            $existingAdmin = Usuario::query()->where('email', $admin['email'])->first();

            if ($existingAdmin !== null) {
                if ($existingAdmin->rol_id !== $adminRole->getKey()) {
                    throw new RuntimeException('ADMIN_EMAIL is already assigned to a different role.');
                }

                return;
            }

            Usuario::query()->create([
                'rol_id' => $adminRole->getKey(),
                'nombre' => $admin['name'],
                'email' => $admin['email'],
                'password' => $admin['password'],
                'estado' => true,
            ]);
        });
    }
}
