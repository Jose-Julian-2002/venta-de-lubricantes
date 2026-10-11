<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\CompraDetalle;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Database\Seeders\SalesPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SalesPosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_sale_uses_catalog_prices_and_renders_printable_snapshot_ticket(): void
    {
        $usuario = $this->signIn();
        $producto = Producto::factory()->create(['nombre' => '<script>aceite</script>', 'stock_actual' => 10, 'precio_venta_menor' => '12.35']);
        $this->post(route('admin.ventas.store'), $this->saleData($producto))->assertSessionHasNoErrors();
        $venta = Venta::query()->firstOrFail();

        $this->assertSame('24.70', $venta->total);
        $this->assertSame($usuario->id, $venta->usuario_id);
        $this->assertSame(8, $producto->fresh()->stock_actual);
        $this->assertDatabaseHas('stock_movimientos', ['producto_id' => $producto->id, 'tipo' => 'salida', 'motivo' => 'venta', 'stock_anterior' => 10, 'stock_nuevo' => 8]);
        $producto->update(['nombre' => 'Nombre cambiado', 'precio_venta_menor' => '99.00']);
        $this->get(route('admin.ventas.show', $venta))->assertOk()->assertSee('&lt;script&gt;aceite&lt;/script&gt;', false)->assertDontSee('<script>aceite</script>', false)->assertDontSee('Nombre cambiado')->assertSee('24,70')->assertSee('Imprimir ticket');
        $this->get(route('admin.ventas.index'))->assertOk()->assertSee($venta->cliente->nombre);
    }

    /** @return array<string, array{string, mixed, string}> */
    public static function invalidInputs(): array
    {
        return [
            'cliente desconocido' => ['cliente_id', 999999, 'cliente_id'],
            'pago desconocido' => ['tipo_pago', 'tarjeta', 'tipo_pago'],
            'carrito vacío' => ['detalles', [], 'detalles'],
            'cantidad cero' => ['detalles.0.cantidad', 0, 'detalles.0.cantidad'],
            'cantidad fraccionada' => ['detalles.0.cantidad', 1.5, 'detalles.0.cantidad'],
            'precio desconocido' => ['detalles.0.tipo_precio', 'gratis', 'detalles.0.tipo_precio'],
            'total falsificado' => ['total', '0.01', 'total'],
            'precio falsificado' => ['detalles.0.precio_unitario', '0.01', 'detalles.0'],
            'token inválido' => ['token', 'x', 'token'],
        ];
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_sale_never_changes_stock(string $field, mixed $value, string $error): void
    {
        $this->signIn();
        $producto = Producto::factory()->create(['stock_actual' => 10]);
        $data = $this->saleData($producto);
        data_set($data, $field, $value);
        $this->post(route('admin.ventas.store'), $data)->assertSessionHasErrors($error);
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
        $this->assertSame(10, $producto->fresh()->stock_actual);
    }

    public function test_box_sale_uses_base_units_for_stock_and_wholesale_price(): void
    {
        $this->signIn();
        $producto = Producto::factory()->create(['stock_actual' => 24, 'precio_venta_mayor' => '10.00']);
        CompraDetalle::factory()->for($producto, 'producto')->create(['unidades_por_caja' => 24]);
        CompraDetalle::factory()->for($producto, 'producto')->create(['unidades_por_caja' => 12]);
        $data = $this->saleData($producto);
        $data['detalles'][0] = ['producto_id' => $producto->id, 'cantidad' => 12, 'tipo_precio' => 'mayor', 'presentacion' => 'caja'];

        $this->post(route('admin.ventas.store'), $data)->assertSessionHasNoErrors();

        $this->assertSame('120.00', Venta::query()->firstOrFail()->total);
        $this->assertSame(12, $producto->fresh()->stock_actual);
        $this->assertDatabaseHas('venta_detalles', ['producto_id' => $producto->id, 'cantidad' => 12, 'precio_unitario' => '10.00', 'subtotal' => '120.00']);
        $this->assertDatabaseHas('stock_movimientos', ['producto_id' => $producto->id, 'cantidad' => 12, 'stock_anterior' => 24, 'stock_nuevo' => 12]);
    }

    /** @return array<string, array{int|null, int}> */
    public static function invalidBoxes(): array
    {
        return ['factor no configurado' => [null, 12], 'caja incompleta' => [12, 13]];
    }

    #[DataProvider('invalidBoxes')]
    public function test_invalid_box_sale_rolls_back_all_items(?int $factor, int $cantidad): void
    {
        $this->signIn();
        $primero = Producto::factory()->create(['stock_actual' => 5]);
        $caja = Producto::factory()->create(['stock_actual' => 24, 'precio_venta_mayor' => '10.00']);
        if ($factor !== null) {
            CompraDetalle::factory()->for($caja, 'producto')->create(['unidades_por_caja' => $factor]);
        }
        $data = $this->saleData($primero);
        $data['detalles'][] = ['producto_id' => $caja->id, 'cantidad' => $cantidad, 'tipo_precio' => 'mayor', 'presentacion' => 'caja'];

        $this->post(route('admin.ventas.store'), $data)->assertSessionHasErrors();

        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('venta_detalles', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
        $this->assertSame(5, $primero->fresh()->stock_actual);
        $this->assertSame(24, $caja->fresh()->stock_actual);
    }

    public function test_pos_form_has_general_customer_and_excludes_inactive_products(): void
    {
        $this->seed(SalesPermissionSeeder::class);
        $this->signIn();
        $activo = Producto::factory()->create(['nombre' => 'Producto visible']);
        Producto::factory()->create(['nombre' => 'Producto oculto', 'estado' => false]);
        $this->get(route('admin.ventas.create'))->assertOk()->assertSee('Cliente General')->assertSee($activo->nombre)->assertDontSee('Producto oculto');
    }

    public function test_quick_customer_creation_validates_and_returns_only_public_fields(): void
    {
        $this->signIn();
        $this->postJson(route('admin.clientes.store'), ['nombre' => '', 'telefono' => str_repeat('1', 31)])->assertUnprocessable()->assertJsonValidationErrors(['nombre', 'telefono']);
        $this->postJson(route('admin.clientes.store'), ['nombre' => 'Ana', 'nit_ci' => '123', 'telefono' => '77777777'])->assertCreated()->assertJsonPath('cliente.nombre', 'Ana')->assertJsonPath('cliente.nit_ci', '123');
        $this->assertDatabaseHas('clientes', ['nombre' => 'Ana', 'nit_ci' => '123']);
    }

    public function test_sales_require_login_and_action_permissions(): void
    {
        $this->get(route('admin.ventas.create'))->assertRedirect(route('login'));
        $this->post(route('admin.ventas.store'))->assertRedirect(route('login'));
        $this->signIn([]);
        $this->get(route('admin.ventas.create'))->assertForbidden();
        $this->get(route('admin.ventas.index'))->assertForbidden();
        $this->post(route('admin.ventas.store'))->assertForbidden();
        $this->postJson(route('admin.clientes.store'), ['nombre' => 'Ana'])->assertForbidden();
    }

    public function test_cashier_can_view_own_ticket_but_cannot_view_another_cashiers_ticket(): void
    {
        $usuario = $this->signIn(['ventas.crear']);
        $own = Venta::factory()->for($usuario, 'usuario')->create();
        $other = Venta::factory()->create();
        $this->get(route('admin.ventas.show', $own))->assertOk();
        $this->get(route('admin.ventas.show', $other))->assertForbidden();
        $this->get(route('admin.ventas.index'))->assertForbidden();
    }

    public function test_kardex_sale_origin_link_respects_ticket_permissions(): void
    {
        $this->signIn(['ventas.crear', 'ventas.ver', 'stock.ver']);
        $producto = Producto::factory()->create(['stock_actual' => 5]);
        $this->post(route('admin.ventas.store'), $this->saleData($producto))->assertSessionHasNoErrors();
        $venta = Venta::query()->firstOrFail();
        $ticketUrl = route('admin.ventas.show', $venta);

        $this->get(route('admin.stock.kardex', $producto))->assertOk()->assertSee('Salida')->assertSee('Venta #'.$venta->id)->assertSee('href="'.$ticketUrl.'"', false);

        $this->signIn(['stock.ver']);
        $this->get(route('admin.stock.kardex', $producto))->assertOk()->assertSee('Venta #'.$venta->id)->assertDontSee('href="'.$ticketUrl.'"', false);
    }

    public function test_product_with_sale_detail_cannot_be_deleted_at_zero_stock(): void
    {
        $this->signIn(['productos.eliminar']);
        $producto = Producto::factory()->create(['stock_actual' => 0]);
        VentaDetalle::factory()->for($producto, 'producto')->create();

        $this->delete(route('admin.productos.destroy', $producto))->assertSessionHasErrors('nombre');

        $this->assertModelExists($producto);
        $this->assertDatabaseCount('venta_detalles', 1);
    }

    public function test_user_with_sales_cannot_be_deleted(): void
    {
        $this->signIn(['usuarios.eliminar']);
        $vendedor = Usuario::factory()->create();
        Venta::factory()->for($vendedor, 'usuario')->create();

        $this->delete(route('admin.usuarios.destroy', $vendedor))->assertSessionHasErrors('usuario');

        $this->assertModelExists($vendedor);
        $this->assertDatabaseCount('ventas', 1);
    }

    public function test_stale_stock_returns_informative_error_and_restores_cart(): void
    {
        $this->signIn();
        $producto = Producto::factory()->create(['nombre' => 'Aceite para recuperar', 'stock_actual' => 1]);
        $data = $this->saleData($producto);
        $createUrl = route('admin.ventas.create');

        $response = $this->from($createUrl)->post(route('admin.ventas.store'), $data);

        $response->assertRedirect($createUrl)->assertSessionHasErrors(['detalles.0.cantidad' => 'Stock insuficiente para Aceite para recuperar. Disponible: 1 unidades.'])->assertSessionHasInput('detalles', $data['detalles'])->assertSessionHasInput('cliente_id', $data['cliente_id']);
        $this->followingRedirects()->from($createUrl)->post(route('admin.ventas.store'), $data)->assertOk()->assertSee('Stock insuficiente')->assertSee('Aceite para recuperar');
        $this->assertSame(1, $producto->fresh()->stock_actual);
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
    }

    /** @param array<int, string> $permissions */
    private function signIn(array $permissions = ['ventas.crear', 'ventas.ver']): Usuario
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
    private function saleData(Producto $producto): array
    {
        return ['token' => (string) Str::uuid(), 'cliente_id' => Cliente::factory()->create()->id, 'tipo_pago' => 'efectivo', 'detalles' => [['producto_id' => $producto->id, 'cantidad' => 2, 'tipo_precio' => 'menor']]];
    }
}
