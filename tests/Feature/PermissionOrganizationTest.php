<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PermissionOrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_every_catalog_ability_denies_missing_permission_and_allows_assigned_permission(): void
    {
        $usuario = Usuario::factory()->create();
        foreach (array_keys(config('permissions.catalog')) as $ability) {
            $this->assertTrue(Gate::has($ability), "Falta definir la autorización $ability.");
            $this->assertFalse(Gate::forUser($usuario->fresh())->allows($ability), "Se permite $ability sin asignación.");
            $permiso = Permiso::factory()->create(['nombre' => $ability]);
            $usuario->rol->permisos()->attach($permiso);
            $this->assertTrue(Gate::forUser($usuario->fresh())->allows($ability), "No se permite $ability asignado.");
        }
    }

    public function test_catalog_actions_have_the_correct_route_authorization(): void
    {
        $actions = ['ver' => ['index'], 'crear' => ['create', 'store'], 'editar' => ['edit', 'update'], 'eliminar' => ['destroy']];
        foreach (array_keys(config('permissions.catalog')) as $ability) {
            [$resource, $action] = explode('.', $ability);
            foreach ($actions[$action] as $routeAction) {
                $name = 'admin.'.$resource.'.'.$routeAction;
                $route = Route::getRoutes()->getByName($name);
                $this->assertNotNull($route, "Falta la ruta $name para $ability.");
                $middleware = $name === 'admin.clientes.store' ? 'can:clientes.guardar' : 'can:'.$ability;
                $this->assertContains($middleware, $route->gatherMiddleware(), "Autorización incorrecta en $name.");
            }
        }
        foreach (['categorias', 'marcas', 'productos', 'proveedores', 'roles', 'usuarios'] as $resource) {
            $route = Route::getRoutes()->getByName('admin.'.$resource.'.show');
            if ($route !== null) {
                $this->assertContains('can:'.$resource.'.ver', $route->gatherMiddleware());
            }
        }
        $this->assertContains('can:compras.ver', Route::getRoutes()->getByName('admin.compras.show')->gatherMiddleware());
        $this->assertContains('can:stock.ver', Route::getRoutes()->getByName('admin.stock.kardex')->gatherMiddleware());
        $this->assertContains('can:clientes.guardar', Route::getRoutes()->getByName('admin.clientes.store')->gatherMiddleware());
        $this->assertContains('can:ventas.ticket,venta', Route::getRoutes()->getByName('admin.ventas.show')->gatherMiddleware());
    }

    public function test_ticket_derived_ability_requires_history_permission_or_owner_with_sales_permission(): void
    {
        $owner = Usuario::factory()->create();
        $sale = Venta::factory()->for($owner, 'usuario')->create();
        $other = Usuario::factory()->create();
        $this->assertFalse(Gate::forUser($owner)->allows('ventas.ticket', $sale));
        $create = Permiso::factory()->create(['nombre' => 'ventas.crear']);
        $owner->rol->permisos()->attach($create);
        $other->rol->permisos()->attach($create);
        $this->assertTrue(Gate::forUser($owner->fresh())->allows('ventas.ticket', $sale));
        $this->assertFalse(Gate::forUser($other->fresh())->allows('ventas.ticket', $sale));
        $view = Permiso::factory()->create(['nombre' => 'ventas.ver']);
        $other->rol->permisos()->sync([$view->id]);
        $this->assertTrue(Gate::forUser($other->fresh())->allows('ventas.ticket', $sale));
    }

    public function test_grouped_role_form_preserves_selected_permissions_and_unknown_abilities(): void
    {
        $this->signIn();
        $sale = Permiso::factory()->create(['nombre' => 'ventas.ver']);
        $product = Permiso::factory()->create(['nombre' => 'productos.ver']);
        $stock = Permiso::factory()->create(['nombre' => 'stock.ver']);
        $unknown = Permiso::factory()->create(['nombre' => 'personalizado.exportar']);
        $rol = Rol::factory()->create();
        $rol->permisos()->attach([$sale->id, $unknown->id]);

        $response = $this->get(route('admin.roles.edit', $rol))->assertOk();
        $response->assertSee('Operaciones')->assertSee('Catálogo')->assertSee('Inventario')->assertSee('Administración')->assertSee('Otros permisos');
        $response->assertViewHas('permisosPorSeccion', function (array $sections) use ($sale, $product, $stock, $unknown): bool {
            $this->assertTrue($sections['Operaciones']->contains('id', $sale->id));
            $this->assertTrue($sections['Catálogo']->contains('id', $product->id));
            $this->assertTrue($sections['Inventario']->contains('id', $stock->id));
            $this->assertTrue($sections['Otros permisos']->contains('id', $unknown->id));

            return true;
        });
        $this->assertPermissionChecked($response->getContent(), $sale->id, true);
        $this->assertPermissionChecked($response->getContent(), $unknown->id, true);
        $this->assertPermissionChecked($response->getContent(), $product->id, false);
    }

    public function test_failed_role_edit_restores_new_selections_instead_of_existing_assignments(): void
    {
        $this->signIn();
        $sale = Permiso::factory()->create(['nombre' => 'ventas.ver']);
        $product = Permiso::factory()->create(['nombre' => 'productos.ver']);
        $rol = Rol::factory()->create();
        $rol->permisos()->attach($sale);
        $response = $this->followingRedirects()->from(route('admin.roles.edit', $rol))->put(route('admin.roles.update', $rol), ['nombre' => '', 'permisos' => [$product->id]])->assertOk();
        $this->assertPermissionChecked($response->getContent(), $product->id, true);
        $this->assertPermissionChecked($response->getContent(), $sale->id, false);
        $this->assertSame([$sale->id], $rol->fresh()->permisos->modelKeys());
    }

    private function signIn(): void
    {
        $rol = Rol::factory()->create();
        foreach (['roles.crear', 'roles.editar'] as $name) {
            $rol->permisos()->attach(Permiso::factory()->create(['nombre' => $name]));
        }
        $this->actingAs(Usuario::factory()->for($rol, 'rol')->create());
    }

    private function assertPermissionChecked(string $html, int $id, bool $checked): void
    {
        $matched = preg_match('/<input\\b(?=[^>]*name="permisos\\[\\]")(?=[^>]*value="'.$id.'")[^>]*>/s', $html, $matches);
        $this->assertSame(1, $matched, "Falta el checkbox del permiso $id.");
        $this->assertSame($checked, preg_match('/\\bchecked\\b/', $matches[0]) === 1, "Selección incorrecta del permiso $id.");
    }
}
