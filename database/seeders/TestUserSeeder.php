<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('El usuario de prueba solo puede crearse en local o testing.');
        }

        $password = DB::transaction(function (): ?string {
            if (Usuario::query()->where('email', 'test@example.test')->exists()) {
                return null;
            }

            $permissionIds = collect(array_keys(config('permissions.catalog')))
                ->map(fn (string $name): int => Permiso::query()->firstOrCreate(['nombre' => $name])->getKey())
                ->all();
            $rol = Rol::query()->firstOrCreate(['nombre' => 'Administrador de pruebas']);
            $rol->permisos()->syncWithoutDetaching($permissionIds);
            $password = Str::password(20);

            Usuario::query()->create([
                'rol_id' => $rol->getKey(),
                'nombre' => 'Usuario de prueba',
                'email' => 'test@example.test',
                'password' => $password,
                'estado' => true,
            ]);

            return $password;
        });

        if ($password === null) {
            $this->command?->info('El usuario test@example.test ya existe; se conservaron sus datos.');

            return;
        }

        $this->command?->info('Usuario: test@example.test');
        $this->command?->info('Contraseña: '.$password);
    }
}
