<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Permiso;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @param array<int, string> $permissions */
    private function signIn(array $permissions = ['proveedores.ver', 'proveedores.crear', 'proveedores.editar', 'proveedores.eliminar']): Usuario
    {
        $rol = Rol::factory()->create();
        foreach ($permissions as $permission) {
            $rol->permisos()->attach(Permiso::factory()->create(['nombre' => $permission]));
        }
        $usuario = Usuario::factory()->for($rol, 'rol')->create();
        $this->actingAs($usuario);

        return $usuario;
    }

    public function test_supplier_crud_preserves_contact_details(): void
    {
        $this->signIn();
        $data = ['nombre_empresa' => 'Distribuidora Central', 'nombre' => 'Ana Pérez', 'telefono' => '+591 70000000', 'nit_ci' => '1234567', 'direccion' => 'Avenida Principal 20'];
        $this->get(route('admin.proveedores.create'))->assertOk()->assertSee('Guardar proveedor');
        $this->post(route('admin.proveedores.store'), $data)->assertRedirect(route('admin.proveedores.index'));
        $proveedor = Proveedor::query()->firstOrFail();
        $this->assertDatabaseHas('proveedores', $data);
        $this->get(route('admin.proveedores.index'))->assertOk()->assertSee('Distribuidora Central')->assertSee('Ana Pérez');
        $this->get(route('admin.proveedores.edit', $proveedor))->assertOk()->assertSee('Editar proveedor');
        $this->put(route('admin.proveedores.update', $proveedor), [...$data, 'nombre_empresa' => 'Distribuidora Norte', 'telefono' => null])->assertRedirect(route('admin.proveedores.index'));
        $this->assertDatabaseHas('proveedores', ['id' => $proveedor->id, 'nombre_empresa' => 'Distribuidora Norte', 'telefono' => null]);
        $this->delete(route('admin.proveedores.destroy', $proveedor))->assertRedirect(route('admin.proveedores.index'));
        $this->assertModelMissing($proveedor);
    }

    public function test_supplier_can_be_registered_without_optional_contact_data(): void
    {
        $this->signIn();
        $this->post(route('admin.proveedores.store'), ['nombre_empresa' => 'Distribuidora'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('proveedores', ['nombre_empresa' => 'Distribuidora', 'nombre' => null, 'nit_ci' => null]);
    }

    /** @return array<string, array{string, mixed}> */
    public static function invalidFields(): array
    {
        return [
            'empresa obligatoria' => ['nombre_empresa', ''],
            'empresa demasiado larga' => ['nombre_empresa', str_repeat('a', 151)],
            'contacto demasiado largo' => ['nombre', str_repeat('a', 151)],
            'teléfono demasiado largo' => ['telefono', str_repeat('1', 21)],
            'NIT demasiado largo' => ['nit_ci', str_repeat('1', 21)],
            'dirección demasiado larga' => ['direccion', str_repeat('a', 2001)],
            'empresa no textual' => ['nombre_empresa', ['invalid']],
        ];
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_supplier_fields_are_rejected_on_create_and_update(string $field, mixed $value): void
    {
        $this->signIn();
        $proveedor = Proveedor::factory()->create();
        $data = ['nombre_empresa' => 'Empresa válida', $field => $value];
        $this->post(route('admin.proveedores.store'), $data)->assertSessionHasErrors($field);
        $this->put(route('admin.proveedores.update', $proveedor), $data)->assertSessionHasErrors($field);
        $this->assertDatabaseCount('proveedores', 1);
        $this->assertSame($proveedor->nombre_empresa, $proveedor->fresh()->nombre_empresa);
    }

    public function test_supplier_with_purchases_cannot_be_deleted(): void
    {
        $usuario = $this->signIn();
        $proveedor = Proveedor::factory()->create();
        Compra::factory()->for($proveedor, 'proveedor')->for($usuario, 'usuario')->create();
        $this->from(route('admin.proveedores.index'))->delete(route('admin.proveedores.destroy', $proveedor))->assertSessionHasErrors(['nombre_empresa' => 'No se puede eliminar el proveedor porque tiene compras registradas.']);
        $this->assertModelExists($proveedor);
        $this->assertDatabaseCount('compras', 1);
    }

    public function test_supplier_management_requires_each_action_permission(): void
    {
        $this->signIn([]);
        $proveedor = Proveedor::factory()->create();
        $this->get(route('admin.proveedores.index'))->assertForbidden();
        $this->get(route('admin.proveedores.create'))->assertForbidden();
        $this->get(route('admin.proveedores.edit', $proveedor))->assertForbidden();
        $this->post(route('admin.proveedores.store'), ['nombre_empresa' => 'Empresa'])->assertForbidden();
        $this->put(route('admin.proveedores.update', $proveedor), ['nombre_empresa' => 'Empresa'])->assertForbidden();
        $this->delete(route('admin.proveedores.destroy', $proveedor))->assertForbidden();
        $this->assertModelExists($proveedor);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.proveedores.index'))->assertRedirect(route('login'));
    }
}
