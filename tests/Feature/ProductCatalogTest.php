<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\CatalogPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @param array<int, string>|null $permissions */
    private function signIn(?array $permissions = null): Usuario
    {
        $rol = Rol::factory()->create();
        $ids = [];
        foreach ($permissions ?? array_keys(config('permissions.catalog')) as $name) {
            $ids[] = Permiso::factory()->create(['nombre' => $name])->id;
        }
        $rol->permisos()->sync($ids);
        $usuario = Usuario::factory()->for($rol, 'rol')->create();
        $this->actingAs($usuario);

        return $usuario;
    }

    /** @return array<string, array{string, class-string}> */
    public static function classifications(): array
    {
        return ['categorías' => ['categorias', Categoria::class], 'marcas' => ['marcas', Marca::class]];
    }

    /** @param class-string<Categoria|Marca> $model */
    #[DataProvider('classifications')]
    public function test_classification_crud_and_unique_name_validation(string $resource, string $model): void
    {
        $this->signIn();
        $this->get(route('admin.'.$resource.'.create'))->assertOk()->assertSee('Guardar');
        $this->post(route('admin.'.$resource.'.store'), ['nombre' => 'Lubricantes'])->assertRedirect(route('admin.'.$resource.'.index'));
        $record = $model::query()->where('nombre', 'Lubricantes')->firstOrFail();
        $this->get(route('admin.'.$resource.'.index'))->assertOk()->assertSee('Lubricantes');
        $this->get(route('admin.'.$resource.'.edit', $record))->assertOk()->assertSee('Lubricantes');
        $this->post(route('admin.'.$resource.'.store'), ['nombre' => 'Lubricantes'])->assertSessionHasErrors('nombre');
        $this->post(route('admin.'.$resource.'.store'), ['nombre' => ''])->assertSessionHasErrors('nombre');
        $this->post(route('admin.'.$resource.'.store'), ['nombre' => str_repeat('a', 101)])->assertSessionHasErrors('nombre');
        $this->put(route('admin.'.$resource.'.update', $record), ['nombre' => 'Lubricantes'])->assertSessionHasNoErrors();
        $other = $model::factory()->create(['nombre' => 'Repuestos']);
        $this->put(route('admin.'.$resource.'.update', $record), ['nombre' => $other->nombre])->assertSessionHasErrors('nombre');
        $this->put(route('admin.'.$resource.'.update', $record), ['nombre' => 'Nuevo nombre'])->assertRedirect(route('admin.'.$resource.'.index'));
        $this->assertDatabaseHas($resource, ['id' => $record->id, 'nombre' => 'Nuevo nombre']);
        $this->delete(route('admin.'.$resource.'.destroy', $record))->assertRedirect(route('admin.'.$resource.'.index'));
        $this->assertModelMissing($record);
    }

    /** @param class-string<Categoria|Marca> $model */
    #[DataProvider('classifications')]
    public function test_classification_with_products_cannot_be_deleted(string $resource, string $model): void
    {
        $this->signIn();
        $record = $model::factory()->create();
        Producto::factory()->for($record, $resource === 'categorias' ? 'categoria' : 'marca')->create();
        $this->from(route('admin.'.$resource.'.index'))->delete(route('admin.'.$resource.'.destroy', $record))->assertSessionHasErrors(['nombre' => 'No se puede eliminar porque tiene productos asociados.']);
        $this->assertModelExists($record);
    }

    public function test_product_crud_preserves_stock_and_decimal_prices(): void
    {
        $this->signIn();
        $data = $this->productData();
        $this->get(route('admin.productos.create'))->assertOk()->assertSee('Stock actual');
        $this->post(route('admin.productos.store'), $data)->assertRedirect(route('admin.productos.index'));
        $producto = Producto::query()->firstOrFail();
        $this->assertSame(0, $producto->stock_actual);
        $this->assertSame('25.50', $producto->precio_venta_menor);
        $this->get(route('admin.productos.index'))->assertOk()->assertSee($data['nombre'])->assertSee($producto->categoria->nombre)->assertSee($producto->marca->nombre);
        $this->get(route('admin.productos.edit', $producto))->assertOk()->assertSee('Editar producto');
        $producto->forceFill(['stock_actual' => 7])->save();
        $this->put(route('admin.productos.update', $producto), [...$data, 'nombre' => 'Producto actualizado', 'estado' => '0'])->assertRedirect(route('admin.productos.index'));
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'nombre' => 'Producto actualizado', 'estado' => 0, 'stock_actual' => 7]);
        $this->delete(route('admin.productos.destroy', $producto))->assertSessionHasErrors('nombre');
        $producto->forceFill(['stock_actual' => 0])->save();
        $this->delete(route('admin.productos.destroy', $producto))->assertRedirect(route('admin.productos.index'));
        $this->assertModelMissing($producto);
    }

    /** @return array<string, array{string, mixed}> */
    public static function invalidProductValues(): array
    {
        return [
            'nombre vacío' => ['nombre', ''],
            'nombre largo' => ['nombre', str_repeat('a', 151)],
            'categoría inexistente' => ['categoria_id', 99999],
            'marca inexistente' => ['marca_id', 99999],
            'estado inválido' => ['estado', 'activo'],
            'stock manual' => ['stock_actual', 10],
            'precio negativo' => ['precio_venta_mayor', '-0.01'],
            'precio fuera de rango' => ['precio_venta_menor', '100000000.00'],
            'tres decimales' => ['precio_venta_mayor', '20.123'],
            'precio no numérico' => ['precio_venta_menor', 'abc'],
        ];
    }

    #[DataProvider('invalidProductValues')]
    public function test_invalid_products_are_not_persisted(string $field, mixed $value): void
    {
        $this->signIn();
        $data = [...$this->productData(), $field => $value];
        $this->post(route('admin.productos.store'), $data)->assertSessionHasErrors($field);
        $this->assertDatabaseCount('productos', 0);
        $producto = Producto::factory()->create();
        $this->put(route('admin.productos.update', $producto), $data)->assertSessionHasErrors($field);
        $this->assertSame('30.00', $producto->fresh()->precio_venta_menor);
    }

    public function test_product_filters_combine_and_survive_pagination(): void
    {
        $this->signIn();
        $categoria = Categoria::factory()->create();
        $marca = Marca::factory()->create();
        Producto::factory()->count(16)->for($categoria, 'categoria')->for($marca, 'marca')->create(['nombre' => 'Aceite seleccionado']);
        Producto::factory()->create(['nombre' => 'Filtro excluido']);
        Producto::factory()->for($categoria, 'categoria')->create(['nombre' => 'Aceite otra marca']);
        Producto::factory()->for($marca, 'marca')->create(['nombre' => 'Aceite otra categoría']);
        Producto::factory()->for($categoria, 'categoria')->for($marca, 'marca')->create(['nombre' => 'Refrigerante']);
        $filters = ['buscar' => 'Aceite', 'categoria_id' => $categoria->id, 'marca_id' => $marca->id];
        $this->get(route('admin.productos.index', $filters))->assertOk()
            ->assertSee('Aceite seleccionado')->assertDontSee('Filtro excluido')->assertDontSee('Aceite otra marca')->assertDontSee('Aceite otra categoría')->assertDontSee('Refrigerante')
            ->assertViewHas('productos', fn ($items): bool => $items->total() === 16 && str_contains($items->url(2), 'buscar=Aceite'));
        $this->get(route('admin.productos.index', ['buscar' => 'sin coincidencias']))->assertOk()->assertSee('No se encontraron productos');
        $this->get(route('admin.productos.index', ['categoria_id' => 'incorrecto']))->assertSessionHasErrors('categoria_id');
    }

    public function test_each_product_filter_works_independently_and_zero_is_searchable(): void
    {
        $this->signIn();
        $selected = Producto::factory()->create(['nombre' => 'Aceite 0w20']);
        $other = Producto::factory()->create(['nombre' => 'Refrigerante excluido']);
        foreach ([
            ['buscar' => '0'],
            ['categoria_id' => $selected->categoria_id],
            ['marca_id' => $selected->marca_id],
        ] as $filters) {
            $this->get(route('admin.productos.index', $filters))
                ->assertOk()->assertSee($selected->nombre)->assertDontSee($other->nombre);
        }
    }

    public function test_product_names_are_escaped_in_views(): void
    {
        $this->signIn();
        Producto::factory()->create(['nombre' => '<script>alert(1)</script>']);
        $this->get(route('admin.productos.index'))
            ->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    /** @return array<string, array{array<string, string|null>}> */
    public static function optionalPrices(): array
    {
        return [
            'omitidos' => [[]],
            'vacíos' => [['precio_venta_menor' => '', 'precio_venta_mayor' => '']],
            'nulos' => [['precio_venta_menor' => null, 'precio_venta_mayor' => null]],
            'solo precio menor' => [['precio_venta_menor' => '25.50']],
            'solo precio mayor' => [['precio_venta_mayor' => '20.00']],
            'precios cero' => [['precio_venta_menor' => '0.00', 'precio_venta_mayor' => '0.00']],
        ];
    }

    /** @param array<string, string|null> $prices */
    #[DataProvider('optionalPrices')]
    public function test_product_can_be_created_with_optional_sale_prices(array $prices): void
    {
        $this->signIn();
        $data = $this->productData();
        unset($data['precio_venta_menor'], $data['precio_venta_mayor']);
        $this->post(route('admin.productos.store'), [...$data, ...$prices])
            ->assertRedirect(route('admin.productos.index'))->assertSessionHasNoErrors();
        $producto = Producto::query()->firstOrFail();
        foreach (['precio_venta_menor', 'precio_venta_mayor'] as $field) {
            $this->assertSame(($prices[$field] ?? '') === '' ? null : $prices[$field], $producto->$field);
        }
        $this->assertFalse(Schema::hasColumn('productos', 'precio_compra_referencia'));
        $response = $this->get(route('admin.productos.index'))->assertOk()->assertDontSee('Compra (Bs)');
        if ($producto->precio_venta_menor === null || $producto->precio_venta_mayor === null) {
            $response->assertSee('Sin precio');
        }
    }

    public function test_existing_product_prices_can_be_cleared_without_changing_stock(): void
    {
        $this->signIn();
        $producto = Producto::factory()->create(['stock_actual' => 7]);
        $data = [
            'nombre' => 'edge 5W-30 1L',
            'categoria_id' => $producto->categoria_id,
            'marca_id' => $producto->marca_id,
            'precio_venta_menor' => '',
            'precio_venta_mayor' => '',
            'estado' => '1',
        ];
        $this->put(route('admin.productos.update', $producto), $data)
            ->assertRedirect(route('admin.productos.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id, 'stock_actual' => 7,
            'precio_venta_menor' => null, 'precio_venta_mayor' => null,
        ]);
        $this->get(route('admin.productos.edit', $producto))->assertOk()
            ->assertDontSee('Compra de referencia')
            ->assertSee('sin repetir la marca');
    }

    /** @return array<string, array{string}> */
    public static function namesRepeatingBrand(): array
    {
        return [
            'prefijo' => ['Castrol edge 5W-30 1L'],
            'mayúsculas' => ['CASTROL edge 5W-30 1L'],
            'minúsculas' => ['edge castrol 5W-30 1L'],
            'entre paréntesis' => ['edge 5W-30 1L (Castrol)'],
        ];
    }

    #[DataProvider('namesRepeatingBrand')]
    public function test_product_name_cannot_repeat_the_selected_brand(string $name): void
    {
        $this->signIn();
        $marca = Marca::factory()->create(['nombre' => 'Castrol']);
        $data = [...$this->productData(), 'marca_id' => $marca->id, 'nombre' => $name];
        $message = 'El nombre no debe incluir la marca seleccionada. Indica la línea, viscosidad o presentación.';
        $this->post(route('admin.productos.store'), $data)->assertSessionHasErrors(['nombre' => $message]);
        $this->assertDatabaseCount('productos', 0);
        $producto = Producto::factory()->for($marca, 'marca')->create(['nombre' => 'edge 5W-30 1L']);
        $this->put(route('admin.productos.update', $producto), $data)->assertSessionHasErrors(['nombre' => $message]);
        $this->assertSame('edge 5W-30 1L', $producto->fresh()->nombre);
    }

    public function test_brand_name_inside_a_different_word_does_not_reject_a_product(): void
    {
        $this->signIn();
        $marca = Marca::factory()->create(['nombre' => 'Shell']);
        $this->post(route('admin.productos.store'), [
            ...$this->productData(), 'marca_id' => $marca->id, 'nombre' => 'Shellac 1L',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('productos', ['nombre' => 'Shellac 1L', 'marca_id' => $marca->id]);
    }

    public function test_product_form_explains_missing_classifications(): void
    {
        $this->signIn();
        $this->get(route('admin.productos.create'))->assertOk()->assertSee('Registra al menos una categoría y una marca');
    }

    public function test_catalog_requires_authentication_and_permissions_for_every_action(): void
    {
        foreach (['categorias', 'marcas', 'productos'] as $resource) {
            $this->get(route('admin.'.$resource.'.index'))->assertRedirect(route('login'));
        }
        $this->signIn([]);
        $records = ['categorias' => Categoria::factory()->create(), 'marcas' => Marca::factory()->create(), 'productos' => Producto::factory()->create()];
        foreach ($records as $resource => $record) {
            $this->get(route('admin.'.$resource.'.index'))->assertForbidden();
            $this->get(route('admin.'.$resource.'.create'))->assertForbidden();
            $this->get(route('admin.'.$resource.'.edit', $record))->assertForbidden();
            $this->post(route('admin.'.$resource.'.store'), [])->assertForbidden();
            $this->put(route('admin.'.$resource.'.update', $record), [])->assertForbidden();
            $this->delete(route('admin.'.$resource.'.destroy', $record))->assertForbidden();
        }
    }

    public function test_view_only_user_cannot_modify_products(): void
    {
        $this->signIn(['productos.ver']);
        $producto = Producto::factory()->create();
        $this->get(route('admin.productos.index'))->assertOk()->assertDontSee('Crear producto')->assertDontSee('Eliminar '.$producto->nombre);
        $this->put(route('admin.productos.update', $producto), [])->assertForbidden();
    }

    public function test_catalog_permission_seeding_is_idempotent_and_preserves_existing_permissions(): void
    {
        $rol = Rol::factory()->create(['nombre' => 'Administrador']);
        $permission = Permiso::factory()->create(['nombre' => 'roles.ver']);
        $rol->permisos()->attach($permission);
        $this->seed(CatalogPermissionSeeder::class);
        $this->seed(CatalogPermissionSeeder::class);
        $this->assertDatabaseCount('permisos', 13);
        $this->assertCount(13, $rol->fresh()->permisos);
    }

    /** @return array{nombre: string, categoria_id: int, marca_id: int, precio_venta_menor: string, precio_venta_mayor: string, estado: string} */
    private function productData(): array
    {
        return ['nombre' => 'edge 5W-30 1L', 'categoria_id' => Categoria::factory()->create()->id, 'marca_id' => Marca::factory()->create()->id, 'precio_venta_menor' => '25.50', 'precio_venta_mayor' => '20.00', 'estado' => '1'];
    }
}
