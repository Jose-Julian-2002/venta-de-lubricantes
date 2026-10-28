<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Sucursal;
use App\Models\Usuario;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesBranchFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_filters_sales_without_changing_the_working_branch(): void
    {
        $this->signIn(true);
        $sucursal = Sucursal::factory()->create();
        Venta::factory()->create();
        $branchSale = Venta::factory()->create(['sucursal_id' => $sucursal->id]);

        $this->withSession(['sucursal_id' => 1])
            ->get(route('admin.ventas.index', ['sucursal_id' => $sucursal->id]))
            ->assertOk()->assertSee('Filtrar ventas por sucursal')->assertSee($sucursal->nombre)
            ->assertViewHas('ventas', fn ($ventas): bool => $ventas->modelKeys() === [$branchSale->id])
            ->assertSessionHas('sucursal_id', 1);
    }

    public function test_admin_can_list_all_branches_including_inactive_branch_history(): void
    {
        $this->signIn(true);
        $sucursal = Sucursal::factory()->create(['estado' => false]);
        Venta::factory()->create();
        Venta::factory()->create(['sucursal_id' => $sucursal->id]);

        $this->get(route('admin.ventas.index', ['sucursal_id' => 'todas']))
            ->assertOk()->assertSee('Todas las sucursales')->assertSee($sucursal->nombre)
            ->assertViewHas('ventas', fn ($ventas): bool => $ventas->total() === 2)
            ->assertViewHas('filters', fn ($filters): bool => $filters['sucursal_id'] === 'todas');
    }

    public function test_default_and_cleared_filter_use_the_working_branch(): void
    {
        $this->signIn(true);
        $sucursal = Sucursal::factory()->create();
        Venta::factory()->create();
        $sale = Venta::factory()->create(['sucursal_id' => $sucursal->id]);

        $this->withSession(['sucursal_id' => $sucursal->id])
            ->get(route('admin.ventas.index'))
            ->assertOk()
            ->assertViewHas('ventas', fn ($ventas): bool => $ventas->modelKeys() === [$sale->id])
            ->assertViewHas('filters', fn ($filters): bool => $filters['sucursal_id'] === (string) $sucursal->id);
    }

    public function test_pagination_preserves_branch_filter(): void
    {
        $this->signIn(true);
        $sucursal = Sucursal::factory()->create();
        Venta::factory()->create();
        Venta::factory()->count(16)->create(['sucursal_id' => $sucursal->id]);

        $this->get(route('admin.ventas.index', ['sucursal_id' => $sucursal->id]))
            ->assertOk()->assertViewHas('ventas', function ($ventas) use ($sucursal): bool {
                $this->assertStringContainsString('sucursal_id='.$sucursal->id, $ventas->nextPageUrl());

                return $ventas->total() === 16 && $ventas->count() === 15;
            });
        $this->get(route('admin.ventas.index', ['sucursal_id' => $sucursal->id, 'page' => 2]))
            ->assertOk()->assertViewHas('ventas', fn ($ventas): bool => $ventas->total() === 16 && $ventas->count() === 1);
    }

    public function test_admin_can_open_a_ticket_from_a_filtered_branch_without_switching(): void
    {
        $this->signIn(true);
        $sucursal = Sucursal::factory()->create();
        $sale = Venta::factory()->create(['sucursal_id' => $sucursal->id]);

        $this->withSession(['sucursal_id' => 1])->get(route('admin.ventas.show', $sale))
            ->assertOk()->assertSee($sucursal->nombre)->assertSessionHas('sucursal_id', 1);
    }

    public function test_unknown_branch_filter_is_rejected(): void
    {
        $this->signIn(true);

        $this->get(route('admin.ventas.index', ['sucursal_id' => 999999]))
            ->assertSessionHasErrors(['sucursal_id' => 'Selecciona una sucursal válida para consultar las ventas.']);
    }

    public function test_operator_cannot_filter_other_branches_or_request_all_sales(): void
    {
        $this->signIn(false);
        $sucursal = Sucursal::factory()->create();
        $localSale = Venta::factory()->create();
        Venta::factory()->create(['sucursal_id' => $sucursal->id]);

        $this->get(route('admin.ventas.index', ['sucursal_id' => $sucursal->id]))
            ->assertSessionHasErrors(['sucursal_id' => 'Selecciona una sucursal válida para consultar las ventas.']);
        $this->get(route('admin.ventas.index', ['sucursal_id' => 'todas']))->assertSessionHasErrors('sucursal_id');
        $this->get(route('admin.ventas.index'))->assertOk()
            ->assertDontSee('Filtrar ventas por sucursal')
            ->assertViewHas('ventas', fn ($ventas): bool => $ventas->modelKeys() === [$localSale->id]);
        $this->assertSame([$localSale->id], app(VentaService::class)->paginate(['sucursal_id' => 'todas'])->modelKeys());
    }

    private function signIn(bool $global): void
    {
        $usuario = Usuario::factory()->create();
        $permissions = $global ? ['ventas.ver', 'sucursales.ver'] : ['ventas.ver'];
        foreach ($permissions as $name) {
            $usuario->rol->permisos()->attach(Permiso::factory()->create(['nombre' => $name]));
        }
        $this->actingAs($usuario);
    }
}
