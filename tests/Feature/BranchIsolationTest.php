<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\StockMovimiento;
use App\Models\Sucursal;
use App\Models\SucursalProducto;
use App\Models\Usuario;
use App\Models\Venta;
use App\Services\CompraService;
use App\Services\StockService;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BranchIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_purchase_updates_only_the_current_branch_and_preserves_global_stock(): void
    {
        $sucursal = Sucursal::factory()->create();
        $usuario = $this->signIn($sucursal->id, ['compras.crear']);
        $producto = Producto::factory()->create(['stock_actual' => 20]);

        $this->post(route('admin.compras.store'), $this->purchaseData($producto))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('compras', ['sucursal_id' => $sucursal->id, 'usuario_id' => $usuario->id, 'total' => '60.00']);
        $this->assertDatabaseHas('sucursal_productos', ['sucursal_id' => $sucursal->id, 'producto_id' => $producto->id, 'stock_actual' => 6]);
        $this->assertDatabaseHas('sucursal_productos', ['sucursal_id' => 1, 'producto_id' => $producto->id, 'stock_actual' => 20]);
        $this->assertDatabaseHas('stock_movimientos', ['sucursal_id' => $sucursal->id, 'producto_id' => $producto->id, 'stock_anterior' => 0, 'stock_nuevo' => 6]);
        $this->assertSame(26, $producto->fresh()->stock_actual);
    }

    public function test_sale_cannot_consume_stock_from_another_branch(): void
    {
        $sucursal = Sucursal::factory()->create();
        $this->signIn($sucursal->id, ['ventas.crear']);
        $producto = Producto::factory()->create(['stock_actual' => 20]);

        $this->post(route('admin.ventas.store'), $this->saleData($producto))
            ->assertSessionHasErrors(['detalles.0.cantidad' => "Stock insuficiente para {$producto->nombre}. Disponible: 0 unidades."]);

        $this->assertSame(20, $producto->fresh()->stock_actual);
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
        $this->assertDatabaseMissing('sucursal_productos', ['sucursal_id' => $sucursal->id, 'producto_id' => $producto->id]);
    }

    public function test_sale_decrements_local_stock_and_records_a_local_kardex_balance(): void
    {
        $sucursal = Sucursal::factory()->create();
        $usuario = $this->signIn($sucursal->id, ['ventas.crear', 'ventas.ver']);
        $producto = Producto::factory()->create(['stock_actual' => 20, 'precio_venta_menor' => '15.00']);
        app(CompraService::class)->create($this->purchaseData($producto), $usuario);

        $this->post(route('admin.ventas.store'), $this->saleData($producto))->assertSessionHasNoErrors();

        $venta = Venta::query()->firstOrFail();
        $this->assertSame($sucursal->id, $venta->sucursal_id);
        $this->assertSame('30.00', $venta->total);
        $this->assertSame(24, $producto->fresh()->stock_actual);
        $this->assertDatabaseHas('sucursal_productos', ['sucursal_id' => 1, 'producto_id' => $producto->id, 'stock_actual' => 20]);
        $this->assertDatabaseHas('sucursal_productos', ['sucursal_id' => $sucursal->id, 'producto_id' => $producto->id, 'stock_actual' => 4]);
        $this->assertDatabaseHas('stock_movimientos', ['sucursal_id' => $sucursal->id, 'tipo' => 'salida', 'stock_anterior' => 6, 'stock_nuevo' => 4]);
        $this->get(route('admin.ventas.show', $venta))->assertOk()->assertSee($sucursal->nombre);
    }

    public function test_foreign_branch_sales_purchases_and_users_are_hidden_even_with_module_permissions(): void
    {
        $sucursal = Sucursal::factory()->create();
        $this->signIn($sucursal->id, ['ventas.ver', 'compras.ver', 'usuarios.ver', 'usuarios.editar', 'usuarios.eliminar']);
        $venta = Venta::factory()->create();
        $compra = Compra::factory()->create();
        $usuario = Usuario::factory()->create();

        $this->get(route('admin.ventas.show', $venta))->assertNotFound();
        $this->get(route('admin.compras.show', $compra))->assertNotFound();
        $this->get(route('admin.usuarios.edit', $usuario))->assertNotFound();
        $this->put(route('admin.usuarios.update', $usuario), [
            'nombre' => 'Intruso', 'rol_id' => $usuario->rol_id, 'email' => $usuario->email, 'estado' => '1',
        ])->assertNotFound();
        $this->delete(route('admin.usuarios.destroy', $usuario))->assertNotFound();
        $this->get(route('admin.ventas.index'))->assertOk()->assertViewHas('ventas', fn ($ventas): bool => $ventas->total() === 0);
        $this->get(route('admin.compras.index'))->assertOk()->assertViewHas('compras', fn ($compras): bool => $compras->total() === 0);
        $this->get(route('admin.usuarios.index'))->assertOk()->assertDontSee($usuario->email);
        $this->assertModelExists($usuario);
        $this->assertDatabaseMissing('usuarios', ['nombre' => 'Intruso']);
    }

    public function test_catalog_clients_and_suppliers_are_shared_but_stock_and_kardex_are_local(): void
    {
        $sucursal = Sucursal::factory()->create();
        $this->signIn($sucursal->id, ['productos.ver', 'clientes.ver', 'proveedores.ver', 'stock.ver']);
        $producto = Producto::factory()->create(['stock_actual' => 20]);
        $cliente = Cliente::factory()->create();
        $proveedor = Proveedor::factory()->create();
        StockMovimiento::factory()->for($producto, 'producto')->create(['motivo' => 'Movimiento privado principal']);

        $this->get(route('admin.productos.index'))->assertOk()->assertSee($producto->nombre)
            ->assertViewHas('productos', fn ($productos): bool => $productos->first()->stock_actual === 0);
        $this->get(route('admin.clientes.index'))->assertOk()->assertSee($cliente->nombre);
        $this->get(route('admin.proveedores.index'))->assertOk()->assertSee($proveedor->nombre_empresa);
        $this->get(route('admin.stock.kardex', $producto))->assertOk()->assertDontSee('Movimiento privado principal')
            ->assertViewHas('producto', fn ($record): bool => $record->stock_actual === 0);
        $this->assertSame(0, app(StockService::class)->movements($producto)->total());
    }

    public function test_admin_switches_branch_reports_without_changing_their_assignment(): void
    {
        $sucursal = Sucursal::factory()->create();
        $admin = $this->signIn(1, ['sucursales.ver', 'ventas.ver', 'reportes.ver']);
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
        Venta::factory()->create(['total' => '10.00', 'created_at' => now()]);
        Venta::factory()->create(['sucursal_id' => $sucursal->id, 'total' => '70.00', 'created_at' => now()]);
        Compra::factory()->create(['total' => '999.00', 'fecha_compra' => now()]);
        Compra::factory()->create(['sucursal_id' => $sucursal->id, 'total' => '40.00', 'fecha_compra' => now()]);

        $this->post(route('admin.sucursal-actual.store'), ['sucursal_id' => $sucursal->id])->assertSessionHasNoErrors();

        $this->get(route('admin.dashboard', ['mes' => '2026-10']))->assertOk()
            ->assertViewHas('resumen', fn ($resumen): bool => $resumen['ventasTotal'] === '70.00' && $resumen['ventasCantidad'] === 1);
        $this->get(route('admin.reportes.index', ['tipo' => 'compras', 'desde' => '2026-10-01', 'hasta' => '2026-10-31']))->assertOk()
            ->assertViewHas('resumen', fn ($resumen): bool => $resumen['comprasTotal'] === '40.00' && $resumen['comprasCantidad'] === 1);
        $this->assertSame(1, $admin->fresh()->sucursal_id);
    }

    public function test_stock_report_counts_branch_balances_including_products_without_inventory_rows(): void
    {
        $sucursal = Sucursal::factory()->create();
        $this->signIn($sucursal->id, ['reportes.ver']);
        $producto = Producto::factory()->create(['stock_actual' => 20]);
        Producto::factory()->create(['stock_actual' => 50]);
        SucursalProducto::query()->create(['sucursal_id' => $sucursal->id, 'producto_id' => $producto->id, 'stock_actual' => 3]);

        $this->get(route('admin.reportes.index', ['tipo' => 'stock']))->assertOk()
            ->assertViewHas('resumen', fn ($resumen): bool => $resumen['unidadesStock'] === 3 && $resumen['agotados'] === 1 && $resumen['productosCantidad'] === 2)
            ->assertViewHas('registros', fn ($registros): bool => $registros->sum('stock_actual') === 3);
    }

    public function test_pos_box_size_uses_latest_purchase_of_its_own_branch(): void
    {
        $sucursal = Sucursal::factory()->create();
        $usuario = $this->signIn($sucursal->id, ['ventas.crear']);
        $producto = Producto::factory()->create(['stock_actual' => 12, 'precio_venta_mayor' => '10.00']);
        app(CompraService::class)->create($this->purchaseData($producto), $usuario);
        CompraDetalle::factory()->for($producto, 'producto')->create(['unidades_por_caja' => 24]);

        $productos = app(VentaService::class)->posOptions()['productos'];

        $this->assertSame(6, $productos->first()->unidades_por_caja_sucursal);
        $data = $this->saleData($producto);
        $data['detalles'][0] = ['producto_id' => $producto->id, 'cantidad' => 6, 'tipo_precio' => 'mayor', 'presentacion' => 'caja'];
        $this->post(route('admin.ventas.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sucursal_productos', ['sucursal_id' => $sucursal->id, 'producto_id' => $producto->id, 'stock_actual' => 0]);
    }

    public function test_forged_branch_and_stale_form_are_rejected_without_inventory_changes(): void
    {
        $sucursal = Sucursal::factory()->create();
        $this->signIn($sucursal->id, ['compras.crear', 'ventas.crear']);
        $producto = Producto::factory()->create(['stock_actual' => 20]);

        $this->post(route('admin.compras.store'), [...$this->purchaseData($producto), 'sucursal_id' => 1])->assertSessionHasErrors('sucursal_id');
        $this->post(route('admin.ventas.store'), [...$this->saleData($producto), 'sucursal_id' => 1])->assertSessionHasErrors('sucursal_id');
        $this->post(route('admin.compras.store'), [...$this->purchaseData($producto), 'sucursal_origen' => 1])->assertSessionHasErrors('sucursal_origen');
        $this->post(route('admin.ventas.store'), [...$this->saleData($producto), 'sucursal_origen' => 1])->assertSessionHasErrors('sucursal_origen');

        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
        $this->assertSame(20, $producto->fresh()->stock_actual);
    }

    public function test_inactive_branch_cannot_register_operations(): void
    {
        $sucursal = Sucursal::factory()->create(['estado' => false]);
        $this->signIn($sucursal->id, ['compras.crear', 'ventas.crear']);
        $producto = Producto::factory()->create(['stock_actual' => 20]);

        $this->post(route('admin.compras.store'), $this->purchaseData($producto))->assertForbidden();
        $this->post(route('admin.ventas.store'), $this->saleData($producto))->assertForbidden();

        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(20, $producto->fresh()->stock_actual);
    }

    public function test_admin_operations_use_selected_branch_and_reused_token_cannot_cross_branches(): void
    {
        $sucursal = Sucursal::factory()->create();
        $admin = $this->signIn(1, ['sucursales.ver', 'compras.crear', 'ventas.crear']);
        $producto = Producto::factory()->create(['stock_actual' => 20]);
        $this->post(route('admin.sucursal-actual.store'), ['sucursal_id' => $sucursal->id]);
        $this->post(route('admin.compras.store'), $this->purchaseData($producto))->assertSessionHasNoErrors();
        $data = $this->saleData($producto);
        $this->post(route('admin.ventas.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ventas', ['sucursal_id' => $sucursal->id, 'usuario_id' => $admin->id]);
        $this->post(route('admin.sucursal-actual.store'), ['sucursal_id' => 1]);

        $this->post(route('admin.ventas.store'), $data)->assertSessionHasErrors('token');

        $this->assertDatabaseCount('ventas', 1);
        $this->assertDatabaseHas('sucursal_productos', ['sucursal_id' => 1, 'producto_id' => $producto->id, 'stock_actual' => 20]);
    }

    /** @param list<string> $permissions */
    private function signIn(int $sucursalId, array $permissions): Usuario
    {
        $usuario = Usuario::factory()->create(['sucursal_id' => $sucursalId]);
        foreach ($permissions as $name) {
            $usuario->rol->permisos()->attach(Permiso::factory()->create(['nombre' => $name]));
        }
        $this->actingAs($usuario);

        return $usuario;
    }

    /** @return array<string, mixed> */
    private function purchaseData(Producto $producto): array
    {
        return [
            'proveedor_id' => Proveedor::factory()->create()->id, 'fecha_compra' => now()->format('Y-m-d\\TH:i'),
            'detalles' => [['producto_id' => $producto->id, 'cantidad_cajas' => 1, 'unidades_por_caja' => 6, 'precio_compra_caja' => '60.00']],
        ];
    }

    /** @return array<string, mixed> */
    private function saleData(Producto $producto): array
    {
        return [
            'token' => (string) Str::uuid(), 'cliente_id' => Cliente::factory()->create()->id, 'tipo_pago' => 'efectivo',
            'detalles' => [['producto_id' => $producto->id, 'cantidad' => 2, 'tipo_precio' => 'menor']],
        ];
    }
}
