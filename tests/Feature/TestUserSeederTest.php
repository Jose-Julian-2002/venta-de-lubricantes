<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\TestUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TestUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_an_active_account_with_all_catalog_permissions(): void
    {
        $this->seed(TestUserSeeder::class);

        $usuario = Usuario::query()->where('email', 'test@example.test')->firstOrFail();
        $this->assertTrue($usuario->estado);
        foreach (array_keys(config('permissions.catalog')) as $permission) {
            $this->assertTrue($usuario->hasPermission($permission));
        }
    }

    public function test_rerunning_does_not_replace_an_existing_account_or_password(): void
    {
        $usuario = Usuario::factory()->create(['email' => 'test@example.test']);
        $original = $usuario->fresh()->getAttributes();

        $this->seed(TestUserSeeder::class);

        $this->assertSame($original, $usuario->fresh()->getAttributes());
        $this->assertDatabaseCount('usuarios', 1);
    }

    public function test_rejects_production_without_creating_an_account(): void
    {
        $this->app->instance('env', 'production');

        try {
            app(TestUserSeeder::class)->run();
            $this->fail('La creación del usuario debió rechazarse.');
        } catch (RuntimeException $exception) {
            $this->assertSame('El usuario de prueba solo puede crearse en local o testing.', $exception->getMessage());
        }

        $this->assertDatabaseCount('usuarios', 0);
        $this->assertDatabaseCount('roles', 0);
    }
}
