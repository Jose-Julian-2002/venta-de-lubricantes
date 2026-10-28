<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SucursalMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_migration_assigns_existing_history_and_stock_to_principal(): void
    {
        $producto = Producto::factory()->create(['stock_actual' => 9]);
        $usuario = Usuario::factory()->create();
        $compra = Compra::factory()->for($usuario, 'usuario')->create();
        $venta = Venta::factory()->for($usuario, 'usuario')->create();
        $movimiento = StockMovimiento::factory()->for($producto, 'producto')->for($usuario, 'usuario')->create();
        $migration = require database_path('migrations/2026_10_28_193419_create_sucursales_and_branch_inventory.php');

        $migration->down();
        $migration->up();

        $this->assertSame(1, $usuario->fresh()->sucursal_id);
        $this->assertSame(1, $compra->fresh()->sucursal_id);
        $this->assertSame(1, $venta->fresh()->sucursal_id);
        $this->assertSame(1, $movimiento->fresh()->sucursal_id);
        $this->assertSame(9, $producto->fresh()->stock_actual);
        $this->assertDatabaseHas('sucursal_productos', ['sucursal_id' => 1, 'producto_id' => $producto->id, 'stock_actual' => 9]);
    }
}
