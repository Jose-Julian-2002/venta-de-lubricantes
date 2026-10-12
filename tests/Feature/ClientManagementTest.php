<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_client_crud_renders_and_persists_optional_contact_fields(): void
    {
        $this->signIn();
        $data = ['nombre' => 'Ana Pérez', 'nit_ci' => '123456', 'telefono' => '77777777'];
        $this->get(route('admin.clientes.create'))->assertOk()->assertSee('Nombre');
        $this->post(route('admin.clientes.store'), $data)->assertSessionHasNoErrors()->assertRedirect(route('admin.clientes.index'));
        $cliente = Cliente::query()->where('nombre', $data['nombre'])->firstOrFail();
        $this->get(route('admin.clientes.index'))->assertOk()->assertSee($data['nombre'])->assertSee($data['nit_ci']);
        $this->get(route('admin.clientes.edit', $cliente))->assertOk()->assertSee($data['telefono']);
        $this->put(route('admin.clientes.update', $cliente), ['nombre' => 'Ana actualizada', 'nit_ci' => '', 'telefono' => ''])->assertSessionHasNoErrors()->assertRedirect(route('admin.clientes.index'));
        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'nombre' => 'Ana actualizada', 'nit_ci' => null, 'telefono' => null]);
        $this->delete(route('admin.clientes.destroy', $cliente))->assertSessionHasNoErrors()->assertRedirect(route('admin.clientes.index'));
        $this->assertModelMissing($cliente);
    }

    public function test_client_search_matches_name_nit_and_phone_and_empty_state(): void
    {
        $this->signIn(['clientes.ver']);
        $cliente = Cliente::factory()->create(['nombre' => 'Cliente objetivo', 'nit_ci' => '450012', 'telefono' => '70000123']);
        Cliente::factory()->create(['nombre' => 'Cliente excluido', 'nit_ci' => '9999', 'telefono' => '8888']);
        foreach (['objetivo', '450012', '70000123', '0'] as $search) {
            $this->get(route('admin.clientes.index', ['buscar' => $search]))->assertOk()->assertSee($cliente->nombre)->assertDontSee('Cliente excluido');
        }
        $this->get(route('admin.clientes.index', ['buscar' => 'inexistente']))->assertOk()->assertViewHas('clientes', fn ($items): bool => $items->total() === 0);
    }

    /** @return array<string, array{string, mixed}> */
    public static function invalidInputs(): array
    {
        return ['nombre vacío' => ['nombre', ''], 'nombre largo' => ['nombre', str_repeat('a', 151)], 'nit largo' => ['nit_ci', str_repeat('1', 51)], 'teléfono largo' => ['telefono', str_repeat('1', 31)]];
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_client_create_and_update_are_rejected(string $field, mixed $value): void
    {
        $this->signIn();
        $data = ['nombre' => 'Cliente válido', 'nit_ci' => '123', 'telefono' => '777', $field => $value];
        $this->post(route('admin.clientes.store'), $data)->assertSessionHasErrors($field);
        $this->assertDatabaseCount('clientes', 0);
        $cliente = Cliente::factory()->create(['nombre' => 'Nombre original']);
        $this->put(route('admin.clientes.update', $cliente), $data)->assertSessionHasErrors($field);
        $this->assertSame('Nombre original', $cliente->fresh()->nombre);
    }

    public function test_general_customer_cannot_be_renamed_or_deleted(): void
    {
        $this->signIn();
        $general = Cliente::factory()->create(['nombre' => 'Cliente General']);
        $this->put(route('admin.clientes.update', $general), ['nombre' => 'Otro nombre'])->assertSessionHasErrors('nombre');
        $this->delete(route('admin.clientes.destroy', $general))->assertSessionHasErrors('nombre');
        $this->assertSame('Cliente General', $general->fresh()->nombre);
    }

    public function test_client_with_sales_history_cannot_be_deleted(): void
    {
        $this->signIn();
        $cliente = Cliente::factory()->create();
        Venta::factory()->for($cliente, 'cliente')->create();
        $this->delete(route('admin.clientes.destroy', $cliente))->assertSessionHasErrors('nombre');
        $this->assertModelExists($cliente);
        $this->assertDatabaseCount('ventas', 1);
    }

    public function test_client_routes_require_authentication_and_action_permissions(): void
    {
        $cliente = Cliente::factory()->create();
        $this->get(route('admin.clientes.index'))->assertRedirect(route('login'));
        $this->post(route('admin.clientes.store'), ['nombre' => 'Ana'])->assertRedirect(route('login'));
        $this->signIn([]);
        $this->get(route('admin.clientes.index'))->assertForbidden();
        $this->get(route('admin.clientes.create'))->assertForbidden();
        $this->get(route('admin.clientes.edit', $cliente))->assertForbidden();
        $this->post(route('admin.clientes.store'), ['nombre' => 'Ana'])->assertForbidden();
        $this->postJson(route('admin.clientes.store'), ['nombre' => 'Ana'])->assertForbidden();
        $this->put(route('admin.clientes.update', $cliente), ['nombre' => 'Ana'])->assertForbidden();
        $this->delete(route('admin.clientes.destroy', $cliente))->assertForbidden();
    }

    public function test_cashier_can_quick_create_json_client_without_client_management_permissions(): void
    {
        $this->signIn(['ventas.crear']);
        $cliente = Cliente::factory()->create();
        $this->postJson(route('admin.clientes.store'), ['nombre' => 'Cliente rápido'])->assertCreated()->assertJsonPath('cliente.nombre', 'Cliente rápido');
        $this->post(route('admin.clientes.store'), ['nombre' => 'Cliente del vendedor'])->assertSessionHasNoErrors()->assertRedirect(route('admin.ventas.create'));
        $this->get(route('admin.clientes.index'))->assertForbidden();
        $this->get(route('admin.clientes.create'))->assertForbidden();
        $this->get(route('admin.clientes.edit', $cliente))->assertForbidden();
        $this->put(route('admin.clientes.update', $cliente), ['nombre' => 'Cambio'])->assertForbidden();
        $this->delete(route('admin.clientes.destroy', $cliente))->assertForbidden();
    }

    public function test_client_names_are_escaped_in_listing(): void
    {
        $this->signIn(['clientes.ver']);
        Cliente::factory()->create(['nombre' => '<script>cliente</script>']);
        $this->get(route('admin.clientes.index'))->assertOk()->assertSee('&lt;script&gt;cliente&lt;/script&gt;', false)->assertDontSee('<script>cliente</script>', false);
    }

    /** @param array<int, string> $permissions */
    private function signIn(array $permissions = ['clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar']): void
    {
        $rol = Rol::factory()->create();
        foreach ($permissions as $name) {
            $rol->permisos()->attach(Permiso::query()->firstOrCreate(['nombre' => $name]));
        }
        $this->actingAs(Usuario::factory()->for($rol, 'rol')->create());
    }
}
