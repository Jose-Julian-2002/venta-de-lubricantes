<?php

namespace Tests\Feature;

use App\Models\CompraDetalle;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\StockMovimiento;
use App\Models\Usuario;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_month_summary_uses_local_boundaries_completed_sales_and_unit_rankings(): void
    {
        $this->signIn();
        $popular = Producto::factory()->create(['nombre' => 'Aceite popular']);
        $expensive = Producto::factory()->create(['nombre' => 'Filtro caro']);
        $this->purchaseCost($popular, '2026-09-01 12:00:00', '5.00');
        $this->purchaseCost($expensive, '2026-09-01 12:00:00', '50.00');
        $this->sale($popular, '2026-10-01 04:00:00', 3, '10.00', '30.00');
        $this->sale($popular, '2026-11-01 03:59:59', 2, '10.00', '20.00');
        $this->sale($expensive, '2026-10-15 12:00:00', 1, '100.00', '100.00');
        $this->sale($popular, '2026-10-01 03:59:59', 50, '10.00', '500.00');
        $this->sale($popular, '2026-11-01 04:00:00', 50, '10.00', '500.00');
        $this->sale($popular, '2026-10-15 12:00:00', 50, '10.00', '500.00', 'anulada');

        $this->get(route('admin.dashboard', ['mes' => '2026-10']))->assertOk()->assertViewHas('resumen', function (array $resumen) use ($popular): bool {
            $this->assertSame('2026-10', $resumen['mes']);
            $this->assertSame('150.00', $resumen['ventasTotal']);
            $this->assertSame('75.00', $resumen['gananciaTotal']);
            $this->assertSame(3, $resumen['ventasCantidad']);
            $this->assertSame(6, $resumen['unidadesVendidas']);
            $this->assertSame(0, $resumen['costosPendientes']);
            $this->assertSame('Aceite popular', $resumen['productoMasVendido']['nombre']);
            $this->assertSame(5, $resumen['productoMasVendido']['unidades']);
            $this->assertSame($popular->marca->nombre, $resumen['marcaMasVendida']['nombre']);

            return true;
        });
    }

    public function test_later_purchase_does_not_rewrite_historical_profit(): void
    {
        $this->signIn();
        $producto = Producto::factory()->create();
        $this->purchaseCost($producto, '2026-10-01 12:00:00', '4.00');
        $this->sale($producto, '2026-10-05 12:00:00', 3, '10.00', '30.00');
        $this->purchaseCost($producto, '2026-10-10 12:00:00', '9.00');

        $this->get(route('admin.dashboard', ['mes' => '2026-10']))->assertOk()->assertViewHas('resumen', fn (array $resumen): bool => $resumen['gananciaTotal'] === '18.00');
    }

    public function test_charts_show_only_top_five_in_descending_unit_order(): void
    {
        $this->signIn();
        for ($quantity = 1; $quantity <= 6; $quantity++) {
            $producto = Producto::factory()->create(['nombre' => 'Producto '.$quantity]);
            $this->sale($producto, '2026-10-05 12:00:00', $quantity, '10.00', (string) ($quantity * 10));
        }

        $this->get(route('admin.dashboard', ['mes' => '2026-10']))->assertOk()
            ->assertSee('Top 5 marcas')->assertSee('Top 5 productos')->assertSee('Ventas totales')->assertSee('Ganancia bruta estimada')
            ->assertViewHas('resumen', function (array $resumen): bool {
                $this->assertSame([6, 5, 4, 3, 2], array_column($resumen['productosTop'], 'unidades'));
                $this->assertSame([6, 5, 4, 3, 2], array_column($resumen['marcasTop'], 'unidades'));

                return true;
            });
    }

    public function test_missing_historical_cost_reports_pending_units_without_inventing_profit(): void
    {
        $this->signIn();
        $producto = Producto::factory()->create();
        $this->sale($producto, '2026-10-05 12:00:00', 3, '10.00', '30.00');
        $this->purchaseCost($producto, '2026-10-10 12:00:00', '9.00');

        $this->get(route('admin.dashboard', ['mes' => '2026-10']))->assertOk()->assertViewHas('resumen', fn (array $resumen): bool => $resumen['gananciaTotal'] === null && $resumen['costosPendientes'] === 3 && $resumen['ventasTotal'] === '30.00');
    }

    public function test_empty_month_has_zero_totals_and_no_rankings(): void
    {
        $this->signIn();
        $this->get(route('admin.dashboard', ['mes' => '2026-10']))->assertOk()->assertViewHas('resumen', fn (array $resumen): bool => $resumen['ventasTotal'] === '0.00' && $resumen['gananciaTotal'] === '0.00' && $resumen['ventasCantidad'] === 0 && $resumen['productoMasVendido'] === null && $resumen['marcaMasVendida'] === null);
    }

    public function test_default_month_uses_bolivian_time_at_utc_month_boundary(): void
    {
        $this->signIn();
        $this->travelTo(Carbon::parse('2026-11-01 02:00:00', 'UTC'));
        $this->get(route('admin.dashboard'))->assertOk()->assertViewHas('resumen', fn (array $resumen): bool => $resumen['mes'] === '2026-10');
        $this->travelBack();
    }

    public function test_invalid_month_is_rejected(): void
    {
        $this->signIn();
        $this->get(route('admin.dashboard', ['mes' => '2026-13']))->assertSessionHasErrors('mes');
    }

    public function test_dashboard_requires_login_and_hides_sales_summary_without_permission(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->signIn(['stock.ver']);
        $this->get(route('admin.dashboard'))->assertOk()->assertViewHas('resumen', null)->assertSee('Inventario')->assertDontSee('href="'.route('admin.ventas.index').'"', false)->assertDontSee('href="'.route('admin.roles.index').'"', false);
    }

    public function test_navigation_groups_and_rankings_escape_product_and_brand_names(): void
    {
        $this->signIn(['ventas.ver', 'ventas.crear', 'productos.ver', 'stock.ver', 'roles.ver']);
        $producto = Producto::factory()->create(['nombre' => '<script>aceite</script>']);
        $producto->marca->update(['nombre' => '<script>marca</script>']);
        $this->sale($producto, '2026-10-05 12:00:00', 3, '10.00', '30.00');
        $this->get(route('admin.dashboard', ['mes' => '2026-10']))->assertOk()->assertSee('Operaciones')->assertSee('Catálogo')->assertSee('Inventario')->assertSee('Administración')->assertSee('&lt;script&gt;aceite&lt;/script&gt;', false)->assertSee('&lt;script&gt;marca&lt;/script&gt;', false)->assertDontSee('<script>aceite</script>', false)->assertDontSee('<script>marca</script>', false);
    }

    /** @param array<int, string> $permissions */
    private function signIn(array $permissions = ['ventas.ver']): Usuario
    {
        $rol = Rol::factory()->create();
        foreach ($permissions as $permission) {
            $rol->permisos()->attach(Permiso::query()->firstOrCreate(['nombre' => $permission], ['descripcion' => $permission]));
        }
        $usuario = Usuario::factory()->for($rol, 'rol')->create();
        $this->actingAs($usuario);

        return $usuario;
    }

    private function sale(Producto $producto, string $date, int $quantity, string $price, string $total, string $state = 'completada'): Venta
    {
        $venta = Venta::factory()->create(['created_at' => $date, 'total' => $total, 'estado' => $state]);
        VentaDetalle::factory()->for($venta, 'venta')->for($producto, 'producto')->create(['cantidad' => $quantity, 'precio_unitario' => $price, 'subtotal' => $total, 'nombre_producto' => $producto->nombre, 'marca_producto' => $producto->marca->nombre]);

        return $venta;
    }

    private function purchaseCost(Producto $producto, string $registeredAt, string $unitCost): void
    {
        $detalle = CompraDetalle::factory()->for($producto, 'producto')->create(['costo_unitario' => $unitCost]);
        $detalle->compra->update(['fecha_compra' => $registeredAt]);
        StockMovimiento::factory()->for($producto, 'producto')->for($detalle, 'compraDetalle')->create(['created_at' => $registeredAt]);
    }
}
