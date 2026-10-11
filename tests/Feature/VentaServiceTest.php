<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VentaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_uses_authoritative_prices_and_retries_without_double_stock_discount(): void
    {
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['stock_actual' => 8, 'precio_venta_menor' => '0.10']);
        $data = $this->payload($producto, 3);

        $venta = app(VentaService::class)->create($data, $usuario);
        $retry = app(VentaService::class)->create($data, $usuario);

        $this->assertSame($venta->id, $retry->id);
        $this->assertSame('0.30', $venta->total);
        $this->assertSame(5, $producto->refresh()->stock_actual);
        $detalle = $venta->detalles()->sole();
        $this->assertDatabaseHas('stock_movimientos', ['venta_detalle_id' => $detalle->id, 'cantidad' => 3, 'stock_anterior' => 8, 'stock_nuevo' => 5, 'tipo' => 'salida', 'motivo' => 'venta']);
        $this->assertDatabaseCount('ventas', 1);
        $this->assertDatabaseCount('stock_movimientos', 1);
    }

    public function test_insufficient_stock_on_second_item_rolls_back_entire_sale(): void
    {
        $usuario = Usuario::factory()->create();
        $first = Producto::factory()->create(['stock_actual' => 5]);
        $second = Producto::factory()->create(['stock_actual' => 1]);
        $data = $this->payload($first, 2);
        $data['detalles'][] = ['producto_id' => $second->id, 'cantidad' => 2, 'tipo_precio' => 'menor'];

        try {
            app(VentaService::class)->create($data, $usuario);
            $this->fail('La venta debe rechazar stock insuficiente.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('detalles.1.cantidad', $exception->errors());
        }

        $this->assertSame(5, $first->refresh()->stock_actual);
        $this->assertSame(1, $second->refresh()->stock_actual);
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('venta_detalles', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
    }

    public function test_reused_token_with_changed_cart_is_rejected(): void
    {
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['stock_actual' => 5]);
        $data = $this->payload($producto, 1);
        app(VentaService::class)->create($data, $usuario);
        $data['detalles'][0]['cantidad'] = 2;

        try {
            app(VentaService::class)->create($data, $usuario);
            $this->fail('Un identificador no puede confirmar una intención diferente.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('token', $exception->errors());
        }

        $this->assertSame(4, $producto->refresh()->stock_actual);
        $this->assertDatabaseCount('ventas', 1);
        $this->assertDatabaseCount('stock_movimientos', 1);
    }

    public function test_inactive_product_is_rechecked_inside_transaction(): void
    {
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['stock_actual' => 3, 'estado' => false]);

        try {
            app(VentaService::class)->create($this->payload($producto), $usuario);
            $this->fail('La venta debe rechazar productos inactivos.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('detalles.0.producto_id', $exception->errors());
        }

        $this->assertSame(3, $producto->refresh()->stock_actual);
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_missing_selected_price_rejects_sale_without_changes(): void
    {
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['stock_actual' => 3, 'precio_venta_menor' => null]);

        try {
            app(VentaService::class)->create($this->payload($producto), $usuario);
            $this->fail('La venta debe rechazar un precio sin configurar.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('detalles.0.tipo_precio', $exception->errors());
        }

        $this->assertSame(3, $producto->refresh()->stock_actual);
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_total_overflow_rejects_sale_without_stock_changes(): void
    {
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['stock_actual' => 3, 'precio_venta_menor' => '99999999.99']);

        try {
            app(VentaService::class)->create($this->payload($producto, 2), $usuario);
            $this->fail('La venta debe rechazar un total fuera de rango.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('detalles', $exception->errors());
        }

        $this->assertSame(3, $producto->refresh()->stock_actual);
        $this->assertDatabaseCount('ventas', 0);
    }

    /** @return array{token: string, cliente_id: int, tipo_pago: string, detalles: array<int, array{producto_id: int, cantidad: int, tipo_precio: string}>} */
    private function payload(Producto $producto, int $cantidad = 1): array
    {
        return ['token' => (string) Str::uuid(), 'cliente_id' => Cliente::factory()->create()->id, 'tipo_pago' => 'efectivo', 'detalles' => [['producto_id' => $producto->id, 'cantidad' => $cantidad, 'tipo_precio' => 'menor']]];
    }
}
