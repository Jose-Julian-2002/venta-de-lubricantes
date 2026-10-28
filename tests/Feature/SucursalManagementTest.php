<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Usuario;
use Database\Seeders\SucursalPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SucursalManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_can_create_edit_and_select_a_branch(): void
    {
        $this->signIn(['sucursales.ver', 'sucursales.crear', 'sucursales.editar']);
        $this->get(route('admin.sucursales.create'))->assertOk()->assertSee('Crear sucursal');

        $this->post(route('admin.sucursales.store'), [
            'nombre' => 'Sucursal Norte', 'direccion' => 'Avenida Norte 15', 'telefono' => '12345678', 'estado' => '1',
        ])->assertRedirect(route('admin.sucursales.index'));

        $sucursal = Sucursal::query()->where('nombre', 'Sucursal Norte')->firstOrFail();
        $this->get(route('admin.sucursales.edit', $sucursal))->assertOk()->assertSee('Sucursal Norte');
        $this->put(route('admin.sucursales.update', $sucursal), ['nombre' => 'Sucursal Central', 'estado' => '1'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sucursales', ['id' => $sucursal->id, 'nombre' => 'Sucursal Central', 'estado' => true]);

        $this->post(route('admin.sucursal-actual.store'), ['sucursal_id' => $sucursal->id])
            ->assertRedirect(route('admin.dashboard'))->assertSessionHas('sucursal_id', $sucursal->id);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Sucursal Central');
    }

    public function test_branch_fields_validate_required_unique_and_length_limits(): void
    {
        $this->signIn(['sucursales.ver', 'sucursales.crear']);

        $this->post(route('admin.sucursales.store'), [])->assertSessionHasErrors([
            'nombre' => 'Indica el nombre de la sucursal.', 'estado' => 'Selecciona el estado de la sucursal.',
        ]);
        $this->post(route('admin.sucursales.store'), ['nombre' => 'Principal', 'estado' => '1'])
            ->assertSessionHasErrors(['nombre' => 'Ya existe una sucursal con este nombre.']);
        $this->post(route('admin.sucursales.store'), [
            'nombre' => str_repeat('x', 101), 'direccion' => str_repeat('x', 256), 'telefono' => str_repeat('1', 31), 'estado' => 'x',
        ])->assertSessionHasErrors(['nombre', 'direccion', 'telefono', 'estado']);

        $this->assertDatabaseCount('sucursales', 1);
    }

    public function test_branch_name_is_escaped_in_the_interface(): void
    {
        $this->signIn(['sucursales.ver']);
        Sucursal::factory()->create(['nombre' => '<script>alert(1)</script>']);

        $this->get(route('admin.sucursales.index'))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_branch_cannot_be_deactivated_with_active_users(): void
    {
        $this->signIn(['sucursales.ver', 'sucursales.editar']);

        $this->put(route('admin.sucursales.update', Sucursal::PRINCIPAL_ID), ['nombre' => 'Principal', 'estado' => '0'])
            ->assertSessionHasErrors(['estado' => 'Desactiva o reasigna primero los usuarios activos de esta sucursal.']);

        $this->assertDatabaseHas('sucursales', ['id' => Sucursal::PRINCIPAL_ID, 'estado' => true]);
    }

    public function test_unused_branch_can_be_deactivated_and_cannot_be_selected(): void
    {
        $this->signIn(['sucursales.ver', 'sucursales.editar']);
        $sucursal = Sucursal::factory()->create();

        $this->put(route('admin.sucursales.update', $sucursal), ['nombre' => $sucursal->nombre, 'estado' => '0'])
            ->assertSessionHasNoErrors();
        $this->post(route('admin.sucursal-actual.store'), ['sucursal_id' => $sucursal->id])
            ->assertSessionHasErrors(['sucursal_id' => 'La sucursal no existe o está inactiva.']);

        $this->assertDatabaseHas('sucursales', ['id' => $sucursal->id, 'estado' => false]);
        $this->assertModelExists($sucursal);
    }

    public function test_branch_routes_require_permissions_and_reject_session_tampering(): void
    {
        $sucursal = Sucursal::factory()->create();
        $usuario = $this->signIn([]);

        $this->get(route('admin.sucursales.index'))->assertForbidden();
        $this->post(route('admin.sucursales.store'), ['nombre' => 'No autorizada', 'estado' => '1'])->assertForbidden();
        $this->put(route('admin.sucursales.update', $sucursal), ['nombre' => 'No autorizada', 'estado' => '0'])->assertForbidden();
        $this->post(route('admin.sucursal-actual.store'), ['sucursal_id' => $sucursal->id])->assertForbidden();
        $this->withSession(['sucursal_id' => $sucursal->id])->get(route('admin.dashboard'))->assertOk()->assertSee('Principal')->assertDontSee($sucursal->nombre);
        $this->assertDatabaseMissing('sucursales', ['nombre' => 'No autorizada']);
        $this->assertSame(Sucursal::PRINCIPAL_ID, $usuario->fresh()->sucursal_id);
    }

    public function test_admin_can_assign_a_user_to_another_branch(): void
    {
        $this->signIn(['sucursales.ver', 'usuarios.crear', 'usuarios.ver']);
        $sucursal = Sucursal::factory()->create();
        $rol = Rol::factory()->create();

        $this->get(route('admin.usuarios.create'))->assertOk()->assertSee($sucursal->nombre);
        $this->post(route('admin.usuarios.store'), [
            'sucursal_id' => $sucursal->id, 'rol_id' => $rol->id, 'nombre' => 'Operador Norte', 'email' => 'norte@example.test',
            'password' => 'secure-branch-password', 'password_confirmation' => 'secure-branch-password', 'estado' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('usuarios', ['email' => 'norte@example.test', 'sucursal_id' => $sucursal->id]);
    }

    public function test_branch_operator_cannot_assign_users_or_global_roles_outside_their_scope(): void
    {
        $this->signIn(['usuarios.crear']);
        $sucursal = Sucursal::factory()->create();
        $globalRole = Rol::factory()->create();
        $globalRole->permisos()->attach(Permiso::factory()->create(['nombre' => 'sucursales.ver']));

        $this->post(route('admin.usuarios.store'), [
            'sucursal_id' => $sucursal->id, 'rol_id' => $globalRole->id, 'nombre' => 'No autorizado', 'email' => 'forged@example.test',
            'password' => 'secure-branch-password', 'password_confirmation' => 'secure-branch-password', 'estado' => '1',
        ])->assertSessionHasErrors(['sucursal_id', 'rol_id']);

        $this->assertDatabaseMissing('usuarios', ['email' => 'forged@example.test']);
    }

    public function test_branch_role_editor_cannot_grant_global_branch_access(): void
    {
        $usuario = $this->signIn(['roles.crear', 'roles.editar']);
        $permission = Permiso::factory()->create(['nombre' => 'sucursales.ver']);

        $this->post(route('admin.roles.store'), ['nombre' => 'Acceso global', 'permisos' => [$permission->id]])
            ->assertSessionHasErrors('permisos.0');
        $this->put(route('admin.roles.update', $usuario->rol), ['nombre' => 'Acceso global', 'permisos' => [$permission->id]])
            ->assertSessionHasErrors('permisos.0');

        $this->assertFalse($usuario->fresh()->hasPermission('sucursales.ver'));
        $this->assertDatabaseMissing('roles', ['nombre' => 'Acceso global']);
    }

    public function test_branch_permission_seeder_updates_existing_admin_roles_idempotently(): void
    {
        $rol = Rol::factory()->create(['nombre' => 'Administrador']);
        $testRole = Rol::factory()->create(['nombre' => 'Administrador de pruebas']);

        $this->seed(SucursalPermissionSeeder::class);
        $this->seed(SucursalPermissionSeeder::class);

        $this->assertSame(3, $rol->permisos()->count());
        $this->assertSame(3, $testRole->permisos()->count());
        $this->assertDatabaseCount('permisos', 3);
    }

    /** @param list<string> $permissions */
    private function signIn(array $permissions): Usuario
    {
        $usuario = Usuario::factory()->create();
        foreach ($permissions as $name) {
            $usuario->rol->permisos()->attach(Permiso::factory()->create(['nombre' => $name]));
        }
        $this->actingAs($usuario);

        return $usuario;
    }
}
