<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdministrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_to_login_from_admin_panel(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $rol = Rol::factory()->create();
        $usuario = Usuario::factory()->for($rol, 'rol')->create([
            'email' => 'inactive@example.test',
            'password' => 'correct-horse-battery',
            'estado' => false,
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $usuario->email,
            'password' => 'correct-horse-battery',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Las credenciales ingresadas no son válidas o la cuenta está inactiva.',
        ]);
        $this->assertGuest();
    }

    public function test_active_user_can_log_in_and_log_out(): void
    {
        $rol = Rol::factory()->create();
        $usuario = Usuario::factory()->for($rol, 'rol')->create([
            'email' => 'active@example.test',
            'password' => 'correct-horse-battery',
        ]);

        $loginResponse = $this->post(route('login.store'), [
            'email' => $usuario->email,
            'password' => 'correct-horse-battery',
        ]);

        $loginResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($usuario);

        $logoutResponse = $this->post(route('logout'));

        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_deactivated_session_is_logged_out_on_the_next_request(): void
    {
        $usuario = Usuario::factory()->create();
        $usuario->update(['estado' => false]);

        $response = $this->actingAs($usuario->fresh())->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Iniciar sesión');
    }

    public function test_permitted_role_and_user_forms_render(): void
    {
        $administrador = $this->createUsuarioConPermisos([
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
        ]);
        $rol = Rol::factory()->create();
        $usuario = Usuario::factory()->for($rol, 'rol')->create();

        $this->actingAs($administrador)
            ->get(route('admin.roles.create'))
            ->assertOk()
            ->assertSee('Permisos');
        $this->get(route('admin.roles.edit', $rol))->assertOk()->assertSee('Editar rol');
        $this->get(route('admin.usuarios.index'))->assertOk()->assertSee($usuario->email);
        $this->get(route('admin.usuarios.create'))->assertOk()->assertSee('Crear usuario');
        $this->get(route('admin.usuarios.edit', $usuario))->assertOk()->assertSee('Editar usuario');
    }

    public function test_role_routes_require_the_matching_permission(): void
    {
        $usuario = $this->createUsuarioConPermisos([]);

        $this->actingAs($usuario)
            ->get(route('admin.roles.index'))
            ->assertForbidden();

        $permission = Permiso::factory()->create(['nombre' => 'roles.ver']);
        $usuario->rol->permisos()->attach($permission);

        $this->actingAs($usuario->fresh())
            ->get(route('admin.roles.index'))
            ->assertOk();
    }

    public function test_role_creation_saves_selected_permissions(): void
    {
        $usuario = $this->createUsuarioConPermisos(['roles.ver', 'roles.crear']);
        $permission = Permiso::factory()->create(['nombre' => 'usuarios.ver']);

        $response = $this->actingAs($usuario)->post(route('admin.roles.store'), [
            'nombre' => 'Vendedor',
            'permisos' => [$permission->id],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['nombre' => 'Vendedor']);

        $rol = Rol::query()->where('nombre', 'Vendedor')->firstOrFail();
        $this->assertSame([$permission->id], $rol->permisos()->pluck('permisos.id')->all());
    }

    public function test_role_update_replaces_its_permissions(): void
    {
        $administrador = $this->createUsuarioConPermisos(['roles.ver', 'roles.editar']);
        $rol = Rol::factory()->create(['nombre' => 'Operador']);
        $permission = Permiso::factory()->create(['nombre' => 'usuarios.ver']);

        $response = $this->actingAs($administrador)->put(route('admin.roles.update', $rol), [
            'nombre' => 'Supervisor',
            'permisos' => [$permission->id],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['id' => $rol->id, 'nombre' => 'Supervisor']);
        $this->assertSame([$permission->id], $rol->fresh()->permisos()->pluck('permisos.id')->all());
    }

    public function test_unused_role_can_be_deleted(): void
    {
        $administrador = $this->createUsuarioConPermisos(['roles.eliminar']);
        $rol = Rol::factory()->create();

        $response = $this->actingAs($administrador)->delete(route('admin.roles.destroy', $rol));

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $rol->id]);
    }

    public function test_role_with_assigned_users_cannot_be_deleted(): void
    {
        $administrador = $this->createUsuarioConPermisos(['roles.ver', 'roles.eliminar']);
        $rol = Rol::factory()->create();
        Usuario::factory()->for($rol, 'rol')->create();

        $response = $this->actingAs($administrador)
            ->from(route('admin.roles.index'))
            ->delete(route('admin.roles.destroy', $rol));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHasErrors('nombre');
        $this->assertModelExists($rol);
    }

    public function test_user_creation_assigns_a_role_and_hashes_the_password(): void
    {
        $administrador = $this->createUsuarioConPermisos(['usuarios.ver', 'usuarios.crear']);
        $rol = Rol::factory()->create();

        $response = $this->actingAs($administrador)->post(route('admin.usuarios.store'), [
            'rol_id' => $rol->id,
            'nombre' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
            'estado' => '1',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseHas('usuarios', [
            'rol_id' => $rol->id,
            'email' => 'maria@example.test',
            'estado' => true,
        ]);

        $usuario = Usuario::query()->where('email', 'maria@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('secure-password-123', $usuario->password));
    }

    public function test_user_creation_rejects_a_short_password_without_persisting_the_account(): void
    {
        $administrador = $this->createUsuarioConPermisos(['usuarios.crear']);
        $rol = Rol::factory()->create();

        $response = $this->actingAs($administrador)
            ->from(route('admin.usuarios.create'))
            ->post(route('admin.usuarios.store'), [
                'rol_id' => $rol->id,
                'nombre' => 'Cuenta incompleta',
                'email' => 'short-password@example.test',
                'password' => 'short',
                'password_confirmation' => 'short',
                'estado' => '1',
            ]);

        $response->assertRedirect(route('admin.usuarios.create'));
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('usuarios', ['email' => 'short-password@example.test']);
    }

    public function test_user_update_keeps_the_existing_password_when_blank(): void
    {
        $administrador = $this->createUsuarioConPermisos(['usuarios.ver', 'usuarios.editar']);
        $usuario = Usuario::factory()->create([
            'password' => 'existing-secret-123',
        ]);
        $passwordHash = $usuario->password;

        $response = $this->actingAs($administrador)->put(route('admin.usuarios.update', $usuario), [
            'rol_id' => $usuario->rol_id,
            'nombre' => 'Nombre actualizado',
            'email' => $usuario->email,
            'password' => '',
            'password_confirmation' => '',
            'estado' => '0',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertSame($passwordHash, $usuario->fresh()->password);
        $this->assertFalse($usuario->fresh()->estado);
    }

    public function test_user_can_be_deleted_by_a_permitted_administrator(): void
    {
        $administrador = $this->createUsuarioConPermisos(['usuarios.eliminar']);
        $usuario = Usuario::factory()->create();

        $response = $this->actingAs($administrador)->delete(route('admin.usuarios.destroy', $usuario));

        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseMissing('usuarios', ['id' => $usuario->id]);
    }

    public function test_admin_seeder_creates_a_permissioned_account_idempotently(): void
    {
        config([
            'admin.name' => 'Administradora',
            'admin.email' => 'admin@example.test',
            'admin.password' => 'secure-admin-password',
        ]);

        $this->seed(AdminUserSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $usuario = Usuario::query()->where('email', 'admin@example.test')->firstOrFail();

        $this->assertSame('Administrador', $usuario->rol->nombre);
        $this->assertTrue(Hash::check('secure-admin-password', $usuario->password));
        $this->assertCount(count(config('permissions.catalog')), $usuario->rol->permisos);
        $this->assertDatabaseCount('usuarios', 1);
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    private function createUsuarioConPermisos(array $permissionNames): Usuario
    {
        $rol = Rol::factory()->create();
        $permissionIds = collect($permissionNames)
            ->map(fn (string $name): int => Permiso::factory()->create(['nombre' => $name])->getKey())
            ->all();

        $rol->permisos()->sync($permissionIds);

        return Usuario::factory()->for($rol, 'rol')->create();
    }
}
