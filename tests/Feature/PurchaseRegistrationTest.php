<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PurchaseRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_purchase_post_calculates_totals_and_units_and_updates_only_supplied_prices(): void
    {
        $usuario = $this->signIn();
        $first = Producto::factory()->create(['stock_actual' => 7, 'precio_venta_menor' => '30.00', 'precio_venta_mayor' => '20.00']);
        $second = Producto::factory()->create(['precio_venta_menor' => null, 'precio_venta_mayor' => null]);
        $data = $this->purchaseData($first);
        $data['detalles'][0]['nuevo_precio_venta_menor'] = '';
        $data['detalles'][0]['nuevo_precio_venta_mayor'] = '0';
        $data['detalles'][] = [...$this->item($second), 'cantidad_cajas' => 1, 'precio_compra_caja' => '10.15', 'nuevo_precio_venta_menor' => '4.50', 'nuevo_precio_venta_mayor' => null];

        $response = $this->post(route('admin.compras.store'), $data);

        $compra = Compra::query()->firstOrFail();
        $response->assertSessionHasNoErrors()->assertRedirect(route('admin.compras.show', $compra));
        $this->assertSame($usuario->id, $compra->usuario_id);
        $this->assertSame('250.15', $compra->total);
        $this->assertDatabaseCount('compra_detalles', 2);
        $this->assertDatabaseCount('stock_movimientos', 2);
        $this->assertDatabaseHas('compra_detalles', ['producto_id' => $first->id, 'cantidad_total_unidades' => 48, 'subtotal' => '240.00', 'costo_unitario' => '5.00']);
        $this->assertDatabaseHas('stock_movimientos', ['producto_id' => $first->id, 'stock_anterior' => 7, 'stock_nuevo' => 55, 'cantidad' => 48, 'usuario_id' => $usuario->id]);
        $this->assertSame(55, $first->fresh()->stock_actual);
        $this->assertSame('30.00', $first->fresh()->precio_venta_menor);
        $this->assertSame('0.00', $first->fresh()->precio_venta_mayor);
        $this->assertSame('4.50', $second->fresh()->precio_venta_menor);
        $this->assertNull($second->fresh()->precio_venta_mayor);
        $this->get(route('admin.compras.index'))->assertOk()->assertSee($compra->proveedor->nombre_empresa);
        $this->get(route('admin.compras.show', $compra))->assertOk()->assertSee($first->nombre)->assertSee($first->marca->nombre)->assertSee('240,00');
    }

    /** @return array<string, array{string, mixed}> */
    public static function invalidValues(): array
    {
        return [
            'proveedor inexistente' => ['proveedor_id', 999999],
            'fecha inválida' => ['fecha_compra', 'ayer'],
            'detalle vacío' => ['detalles', []],
            'producto inexistente' => ['detalles.0.producto_id', 999999],
            'cajas cero' => ['detalles.0.cantidad_cajas', 0],
            'cajas fraccionadas' => ['detalles.0.cantidad_cajas', 1.5],
            'cajas demasiado grandes' => ['detalles.0.cantidad_cajas', 1000001],
            'unidades negativas' => ['detalles.0.unidades_por_caja', -1],
            'unidades cero' => ['detalles.0.unidades_por_caja', 0],
            'precio negativo' => ['detalles.0.precio_compra_caja', '-0.01'],
            'precio excesivo' => ['detalles.0.precio_compra_caja', '100000000.00'],
            'precio con tres decimales' => ['detalles.0.precio_compra_caja', '10.123'],
            'venta inválida' => ['detalles.0.nuevo_precio_venta_menor', 'abc'],
            'venta negativa' => ['detalles.0.nuevo_precio_venta_mayor', '-1'],
            'total falsificado' => ['total', '1.00'],
            'usuario falsificado' => ['usuario_id', 99999],
            'stock falsificado' => ['detalles.0.stock_actual', 900],
            'costo falsificado' => ['detalles.0.costo_unitario', '0.01'],
        ];
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_or_forged_purchase_inputs_never_change_inventory(string $field, mixed $value): void
    {
        $this->signIn();
        $producto = Producto::factory()->create(['stock_actual' => 9]);
        $data = $this->purchaseData($producto);
        data_set($data, $field, $value);
        $error = in_array($field, ['detalles.0.stock_actual', 'detalles.0.costo_unitario'], true) ? 'detalles.0' : $field;

        $this->post(route('admin.compras.store'), $data)->assertSessionHasErrors($error);

        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseCount('compra_detalles', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
        $this->assertSame(9, $producto->fresh()->stock_actual);
    }

    public function test_inactive_and_duplicate_products_are_rejected(): void
    {
        $this->signIn();
        $producto = Producto::factory()->create(['estado' => false]);
        $this->post(route('admin.compras.store'), $this->purchaseData($producto))->assertSessionHasErrors('detalles.0.producto_id');
        $producto->update(['estado' => true]);
        $data = $this->purchaseData($producto);
        $data['detalles'][] = $data['detalles'][0];

        $this->post(route('admin.compras.store'), $data)->assertSessionHasErrors('detalles.1.producto_id');

        $this->assertDatabaseCount('compras', 0);
        $this->assertSame(0, $producto->fresh()->stock_actual);
    }

    public function test_purchase_form_restores_input_and_shows_spanish_errors(): void
    {
        $this->signIn();
        $producto = Producto::factory()->create();
        $data = $this->purchaseData($producto);
        $data['detalles'][0]['cantidad_cajas'] = 0;

        $response = $this->followingRedirects()->from(route('admin.compras.create'))->post(route('admin.compras.store'), $data);

        $response->assertOk()->assertSee('Agregar producto')->assertSee('debe ser al menos 1')->assertSee('precio_compra_caja')->assertSee($producto->nombre)->assertSee($data['fecha_compra']);
    }

    public function test_purchase_routes_require_authentication_and_action_permissions(): void
    {
        $compra = Compra::factory()->create();
        foreach (['index', 'create', 'show'] as $action) {
            $this->get(route('admin.compras.'.$action, $action === 'show' ? $compra : []))->assertRedirect(route('login'));
        }
        $this->post(route('admin.compras.store'), [])->assertRedirect(route('login'));
        $this->signIn([]);
        foreach (['index', 'create', 'show'] as $action) {
            $this->get(route('admin.compras.'.$action, $action === 'show' ? $compra : []))->assertForbidden();
        }
        $this->post(route('admin.compras.store'), [])->assertForbidden();
        $this->signIn(['compras.ver']);
        $this->get(route('admin.compras.index'))->assertOk()->assertDontSee('Registrar compra');
        $this->get(route('admin.compras.show', $compra))->assertOk();
        $this->get(route('admin.compras.create'))->assertForbidden();
        $this->post(route('admin.compras.store'), [])->assertForbidden();
    }

    /** @param array<int, string> $permissions */
    private function signIn(array $permissions = ['compras.ver', 'compras.crear']): Usuario
    {
        $rol = Rol::factory()->create();
        foreach ($permissions as $permission) {
            $rol->permisos()->attach(Permiso::query()->firstOrCreate(['nombre' => $permission], ['descripcion' => $permission]));
        }
        $usuario = Usuario::factory()->for($rol, 'rol')->create();
        $this->actingAs($usuario);

        return $usuario;
    }

    /** @return array<string, mixed> */
    private function purchaseData(Producto $producto): array
    {
        return ['proveedor_id' => Proveedor::factory()->create()->id, 'fecha_compra' => now()->subMinute()->format('Y-m-d\TH:i'), 'detalles' => [$this->item($producto)]];
    }

    /** @return array<string, mixed> */
    private function item(Producto $producto): array
    {
        return ['producto_id' => $producto->id, 'cantidad_cajas' => 2, 'unidades_por_caja' => 24, 'precio_compra_caja' => '120.00'];
    }
}
