<?php

namespace Tests\Feature;

use App\Models\Compra;
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
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReportesBasicosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_reports_require_authentication_and_explicit_permission(): void
    {
        $this->get(route('admin.reportes.index'))->assertRedirect(route('login'));
        $this->signIn(false);
        $this->get(route('admin.reportes.index'))->assertForbidden();
        $this->signIn();
        $this->get(route('admin.reportes.index'))->assertOk();
    }

    public function test_sales_range_spans_months_and_uses_inclusive_bolivian_dates(): void
    {
        $this->signIn();
        $producto = Producto::factory()->create();
        $this->purchaseCost($producto, '2026-09-01 12:00:00', '4.00');
        $first = $this->sale($producto, '2026-09-30 04:00:00', 2, '10.00', '20.00');
        $last = $this->sale($producto, '2026-10-02 03:59:59', 3, '10.00', '30.00');
        $this->sale($producto, '2026-09-30 03:59:59', 100, '10.00', '1000.00');
        $this->sale($producto, '2026-10-02 04:00:00', 100, '10.00', '1000.00');
        $this->sale($producto, '2026-10-01 12:00:00', 100, '10.00', '1000.00', 'anulada');
        $this->purchaseCost($producto, '2026-10-05 12:00:00', '9.00');

        $this->get(route('admin.reportes.index', ['tipo' => 'ventas', 'desde' => '2026-09-30', 'hasta' => '2026-10-01']))->assertOk()
            ->assertViewHas('resumen', fn (array $summary): bool => $summary['ventasTotal'] === '50.00' && $summary['ventasCantidad'] === 2 && $summary['unidadesVendidas'] === 5 && $summary['gananciaTotal'] === '30.00')
            ->assertViewHas('registros', fn ($rows): bool => $rows->total() === 2 && $rows->getCollection()->modelKeys() === [$last->id, $first->id]);
    }

    public function test_sales_missing_cost_reports_pending_units_and_unknown_profit(): void
    {
        $this->signIn();
        $producto = Producto::factory()->create();
        $this->sale($producto, '2026-10-01 12:00:00', 2, '10.00', '20.00');
        $this->get(route('admin.reportes.index', ['tipo' => 'ventas', 'desde' => '2026-10-01', 'hasta' => '2026-10-01']))->assertOk()
            ->assertViewHas('resumen', fn (array $summary): bool => $summary['gananciaTotal'] === null && $summary['costosPendientes'] === 2);
    }

    public function test_default_sales_report_uses_local_current_month_to_today(): void
    {
        $this->signIn();
        $this->travelTo(Carbon::parse('2026-11-01 02:00:00', 'UTC'));
        $this->get(route('admin.reportes.index'))->assertOk()->assertViewHas('filters', ['tipo' => 'ventas', 'desde' => '2026-10-01', 'hasta' => '2026-10-31']);
        $this->travelBack();
    }

    public function test_purchase_report_filters_business_date_and_sums_exact_totals(): void
    {
        $this->signIn();
        $first = Compra::factory()->create(['fecha_compra' => '2026-09-30 00:00:00', 'total' => '20.15']);
        $last = Compra::factory()->create(['fecha_compra' => '2026-10-01 23:59:59', 'total' => '30.20']);
        Compra::factory()->create(['fecha_compra' => '2026-09-29 23:59:59', 'total' => '999.00']);
        Compra::factory()->create(['fecha_compra' => '2026-10-02 00:00:00', 'total' => '999.00']);
        $this->get(route('admin.reportes.index', ['tipo' => 'compras', 'desde' => '2026-09-30', 'hasta' => '2026-10-01']))->assertOk()
            ->assertViewHas('resumen', fn (array $summary): bool => $summary['comprasTotal'] === '50.35' && $summary['comprasCantidad'] === 2)
            ->assertViewHas('registros', fn ($rows): bool => $rows->getCollection()->modelKeys() === [$last->id, $first->id]);
    }

    public function test_stock_report_includes_inactive_products_and_ignores_date_range(): void
    {
        $this->signIn();
        Producto::factory()->create(['stock_actual' => 7]);
        Producto::factory()->create(['stock_actual' => 5, 'estado' => false]);
        Producto::factory()->create(['stock_actual' => 0]);
        $this->get(route('admin.reportes.index', ['tipo' => 'stock', 'desde' => '2000-01-01', 'hasta' => '2000-01-02']))->assertOk()
            ->assertViewHas('resumen', ['productosCantidad' => 3, 'unidadesStock' => 12, 'agotados' => 1])
            ->assertViewHas('registros', fn ($rows): bool => $rows->total() === 3);
    }

    public function test_report_escapes_client_product_and_supplier_names(): void
    {
        $this->signIn();
        $producto = Producto::factory()->create(['nombre' => '<script>producto</script>']);
        $venta = $this->sale($producto, '2026-10-01 12:00:00', 2, '10.00', '20.00');
        $venta->cliente->update(['nombre' => '<script>cliente</script>']);
        $compra = Compra::factory()->create(['fecha_compra' => '2026-10-01 12:00:00']);
        $compra->proveedor->update(['nombre_empresa' => '<script>proveedor</script>']);
        $range = ['desde' => '2026-10-01', 'hasta' => '2026-10-01'];
        $this->get(route('admin.reportes.index', [...$range, 'tipo' => 'ventas']))->assertOk()->assertSee('&lt;script&gt;cliente&lt;/script&gt;', false)->assertDontSee('<script>cliente</script>', false);
        $this->get(route('admin.reportes.index', [...$range, 'tipo' => 'compras']))->assertOk()->assertSee('&lt;script&gt;proveedor&lt;/script&gt;', false)->assertDontSee('<script>proveedor</script>', false);
        $this->get(route('admin.reportes.index', [...$range, 'tipo' => 'stock']))->assertOk()->assertSee('&lt;script&gt;producto&lt;/script&gt;', false)->assertDontSee('<script>producto</script>', false);
    }

    /** @return array<string, array{array<string, string>, string}> */
    public static function invalidFilters(): array
    {
        return [
            'tipo desconocido' => [['tipo' => 'inexistente'], 'tipo'],
            'fecha inválida' => [['desde' => 'ayer'], 'desde'],
            'fecha final inválida' => [['hasta' => '2026-02-30'], 'hasta'],
            'rango invertido' => [['desde' => '2026-10-20', 'hasta' => '2026-10-01'], 'hasta'],
        ];
    }

    /** @param array<string, string> $filters */
    #[DataProvider('invalidFilters')]
    public function test_invalid_report_filters_are_rejected(array $filters, string $error): void
    {
        $this->signIn();
        $this->get(route('admin.reportes.index', $filters))->assertSessionHasErrors($error);
    }

    private function signIn(bool $permitted = true): void
    {
        $rol = Rol::factory()->create();
        if ($permitted) {
            $rol->permisos()->attach(Permiso::query()->firstOrCreate(['nombre' => 'reportes.ver']));
        }
        $this->actingAs(Usuario::factory()->for($rol, 'rol')->create());
    }

    private function sale(Producto $producto, string $date, int $quantity, string $price, string $total, string $state = 'completada'): Venta
    {
        $venta = Venta::factory()->create(['created_at' => $date, 'total' => $total, 'estado' => $state]);
        VentaDetalle::factory()->for($venta, 'venta')->for($producto, 'producto')->create(['cantidad' => $quantity, 'precio_unitario' => $price, 'subtotal' => $total, 'nombre_producto' => $producto->nombre, 'marca_producto' => $producto->marca->nombre]);

        return $venta;
    }

    private function purchaseCost(Producto $producto, string $date, string $unitCost): void
    {
        $compra = Compra::factory()->create(['fecha_compra' => $date]);
        $detalle = CompraDetalle::factory()->for($compra, 'compra')->for($producto, 'producto')->create(['costo_unitario' => $unitCost]);
        StockMovimiento::factory()->for($producto, 'producto')->for($detalle, 'compraDetalle')->create(['created_at' => $date]);
    }
}
