<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Services\CompraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CompraTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sequential_purchases_accumulate_stock_and_preserve_blank_prices(): void
    {
        $usuario = Usuario::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $producto = Producto::factory()->create(['stock_actual' => 2, 'precio_venta_menor' => null, 'precio_venta_mayor' => '12.00']);
        $data = ['proveedor_id' => $proveedor->id, 'fecha_compra' => now()->format('Y-m-d\TH:i'), 'detalles' => [['producto_id' => $producto->id, 'cantidad_cajas' => 2, 'unidades_por_caja' => 6, 'precio_compra_caja' => '60.00', 'nuevo_precio_venta_menor' => '', 'nuevo_precio_venta_mayor' => null]]];

        app(CompraService::class)->create($data, $usuario);
        app(CompraService::class)->create($data, $usuario);

        $this->assertSame(26, $producto->refresh()->stock_actual);
        $this->assertNull($producto->precio_venta_menor);
        $this->assertSame('12.00', $producto->precio_venta_mayor);
        $this->assertDatabaseHas('stock_movimientos', ['producto_id' => $producto->id, 'stock_anterior' => 2, 'stock_nuevo' => 14]);
        $this->assertDatabaseHas('stock_movimientos', ['producto_id' => $producto->id, 'stock_anterior' => 14, 'stock_nuevo' => 26]);
        $this->assertDatabaseCount('compras', 2);
        $this->assertDatabaseCount('stock_movimientos', 2);
    }

    public function test_purchase_records_exact_amounts_stock_and_only_supplied_prices(): void
    {
        $usuario = Usuario::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $producto = Producto::factory()->create(['stock_actual' => 7, 'precio_venta_menor' => null, 'precio_venta_mayor' => '20.00']);
        $compra = app(CompraService::class)->create([
            'proveedor_id' => $proveedor->id, 'fecha_compra' => now()->format('Y-m-d\TH:i'),
            'detalles' => [['producto_id' => $producto->id, 'cantidad_cajas' => 3, 'unidades_por_caja' => 24, 'precio_compra_caja' => '100.01', 'nuevo_precio_venta_menor' => '0', 'nuevo_precio_venta_mayor' => null]],
        ], $usuario);

        $this->assertSame('300.03', $compra->total);
        $this->assertSame(79, $producto->refresh()->stock_actual);
        $this->assertSame('0.00', $producto->precio_venta_menor);
        $this->assertSame('20.00', $producto->precio_venta_mayor);
        $detalle = $compra->detalles()->sole();
        $this->assertSame('4.17', $detalle->costo_unitario);
        $this->assertDatabaseHas('stock_movimientos', ['producto_id' => $producto->id, 'compra_detalle_id' => $detalle->id, 'usuario_id' => $usuario->id, 'stock_anterior' => 7, 'stock_nuevo' => 79, 'cantidad' => 72, 'tipo' => 'entrada', 'motivo' => 'compra']);
    }

    public function test_failure_after_first_detail_rolls_back_entire_purchase(): void
    {
        $usuario = Usuario::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $first = Producto::factory()->create(['stock_actual' => 5, 'precio_venta_menor' => '10.00']);
        $second = Producto::factory()->create(['stock_actual' => 4294967295]);
        try {
            app(CompraService::class)->create([
                'proveedor_id' => $proveedor->id, 'fecha_compra' => now()->format('Y-m-d\TH:i'),
                'detalles' => [
                    ['producto_id' => $first->id, 'cantidad_cajas' => 1, 'unidades_por_caja' => 12, 'precio_compra_caja' => '100.00', 'nuevo_precio_venta_menor' => '25.00'],
                    ['producto_id' => $second->id, 'cantidad_cajas' => 1, 'unidades_por_caja' => 1, 'precio_compra_caja' => '1.00'],
                ],
            ], $usuario);
            $this->fail('La compra debe rechazar el stock fuera de rango.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('detalles.1.cantidad_cajas', $exception->errors());
        }
        $this->assertSame(5, $first->refresh()->stock_actual);
        $this->assertSame('10.00', $first->precio_venta_menor);
        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseCount('compra_detalles', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
    }

    public function test_purchase_rechecks_active_product_and_rejects_total_overflow(): void
    {
        $usuario = Usuario::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $producto = Producto::factory()->create(['estado' => false]);
        $data = ['proveedor_id' => $proveedor->id, 'fecha_compra' => now()->format('Y-m-d\TH:i'), 'detalles' => [['producto_id' => $producto->id, 'cantidad_cajas' => 2, 'unidades_por_caja' => 1, 'precio_compra_caja' => '99999999.99']]];
        try {
            app(CompraService::class)->create($data, $usuario);
            $this->fail('Un producto inactivo no debe ingresar al inventario.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('detalles.0.producto_id', $exception->errors());
        }
        $producto->update(['estado' => true]);
        try {
            app(CompraService::class)->create($data, $usuario);
            $this->fail('La compra debe rechazar un total fuera de rango.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('detalles', $exception->errors());
        }
        $this->assertSame(0, $producto->refresh()->stock_actual);
        $this->assertDatabaseCount('compras', 0);
    }
}
