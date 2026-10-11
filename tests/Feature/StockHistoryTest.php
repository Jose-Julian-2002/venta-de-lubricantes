<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\StockMovimiento;
use App\Models\Usuario;
use Database\Seeders\PurchasePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @param array<int, string> $permissions */
    private function signIn(array $permissions): Usuario
    {
        $rol = Rol::factory()->create();
        $ids = [];
        foreach ($permissions as $name) {
            $ids[] = Permiso::factory()->create(['nombre' => $name])->id;
        }
        $rol->permisos()->sync($ids);
        $usuario = Usuario::factory()->for($rol, 'rol')->create();
        $this->actingAs($usuario);

        return $usuario;
    }

    public function test_stock_and_kardex_require_authentication_and_permission(): void
    {
        $producto = Producto::factory()->create();
        $this->get(route('admin.stock.index'))->assertRedirect(route('login'));
        $this->get(route('admin.stock.kardex', $producto))->assertRedirect(route('login'));
        $this->signIn([]);
        $this->get(route('admin.stock.index'))->assertForbidden();
        $this->get(route('admin.stock.kardex', $producto))->assertForbidden();
    }

    public function test_inventory_filters_by_product_and_retains_pagination(): void
    {
        $this->signIn(['stock.ver']);
        $producto = Producto::factory()->create(['nombre' => 'Aceite objetivo']);
        Producto::factory()->create(['nombre' => 'Refrigerante excluido']);
        $this->get(route('admin.stock.index', ['buscar' => 'Aceite', 'marca_id' => $producto->marca_id]))
            ->assertOk()->assertSee('Aceite objetivo')->assertDontSee('Refrigerante excluido')
            ->assertSee('Ver Kardex');
    }

    public function test_kardex_is_scoped_to_product_and_shows_balances_and_actor(): void
    {
        $actor = $this->signIn(['stock.ver']);
        $producto = Producto::factory()->create(['nombre' => 'Aceite seleccionado', 'stock_actual' => 24]);
        StockMovimiento::factory()->for($producto, 'producto')->for($actor, 'usuario')->create([
            'tipo' => 'entrada', 'motivo' => 'compra', 'cantidad' => 24, 'stock_anterior' => 0, 'stock_nuevo' => 24,
        ]);
        StockMovimiento::factory()->create(['motivo' => 'movimiento excluido']);
        $this->get(route('admin.stock.kardex', $producto))->assertOk()
            ->assertSee('Aceite seleccionado')->assertSee('24 unidades')->assertSee($actor->nombre)
            ->assertSee('Stock anterior')->assertSee('Stock nuevo')->assertDontSee('movimiento excluido');
        $other = Producto::factory()->create();
        $this->get(route('admin.stock.kardex', $other))->assertOk()->assertSee('Este producto aún no tiene movimientos');
    }

    public function test_product_with_audit_history_cannot_be_deleted_even_at_zero_stock(): void
    {
        $actor = $this->signIn(['productos.eliminar']);
        $producto = Producto::factory()->create(['stock_actual' => 0]);
        StockMovimiento::factory()->for($producto, 'producto')->for($actor, 'usuario')->create();
        $this->delete(route('admin.productos.destroy', $producto))->assertSessionHasErrors('nombre');
        $this->assertModelExists($producto);
    }

    public function test_user_with_purchases_cannot_be_deleted(): void
    {
        $this->signIn(['usuarios.eliminar']);
        $usuario = Usuario::factory()->create();
        Compra::factory()->for($usuario, 'usuario')->create();
        $this->delete(route('admin.usuarios.destroy', $usuario))->assertSessionHasErrors('usuario');
        $this->assertModelExists($usuario);
    }

    public function test_purchase_permissions_are_seeded_idempotently_for_existing_admin(): void
    {
        $rol = Rol::factory()->create(['nombre' => 'Administrador']);
        $existing = Permiso::factory()->create(['nombre' => 'roles.ver']);
        $rol->permisos()->attach($existing);
        $this->seed(PurchasePermissionSeeder::class);
        $this->seed(PurchasePermissionSeeder::class);
        $this->assertDatabaseCount('permisos', 8);
        $this->assertCount(8, $rol->fresh()->permisos);
    }
}
