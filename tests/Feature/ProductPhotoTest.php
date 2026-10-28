<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\ProductoService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_photo_is_optional_when_creating_a_product(): void
    {
        $this->signIn();

        $this->post(route('admin.productos.store'), $this->data())->assertSessionHasNoErrors();

        $this->assertDatabaseHas('productos', ['nombre' => 'Aceite 10W-40', 'foto_path' => null, 'foto_miniatura_path' => null]);
    }

    public function test_upload_creates_resized_webp_and_thumbnail_and_renders_lazy_image(): void
    {
        $this->signIn();
        Storage::fake('public');
        $file = UploadedFile::fake()->image('foto.png', 1600, 800);

        $this->post(route('admin.productos.store'), [...$this->data(), 'foto' => $file])->assertSessionHasNoErrors();

        $producto = Producto::query()->firstOrFail();
        Storage::disk('public')->assertExists([$producto->foto_path, $producto->foto_miniatura_path]);
        $full = getimagesizefromstring(Storage::disk('public')->get($producto->foto_path));
        $thumb = getimagesizefromstring(Storage::disk('public')->get($producto->foto_miniatura_path));
        $this->assertSame([1200, 600, IMAGETYPE_WEBP], array_slice($full, 0, 3));
        $this->assertSame([320, 160, IMAGETYPE_WEBP], array_slice($thumb, 0, 3));
        $this->assertCount(2, Storage::disk('public')->allFiles());
        $this->get(route('admin.productos.index'))->assertOk()->assertSee($producto->fotoMiniaturaUrl())->assertSee('loading="lazy"', false);
        $this->get(route('admin.productos.edit', $producto))->assertOk()->assertSee('Quitar foto actual');
    }

    public function test_replacing_a_photo_removes_previous_files_without_upscaling(): void
    {
        $this->signIn();
        Storage::fake('public');
        $data = $this->data();
        $this->post(route('admin.productos.store'), [...$data, 'foto' => UploadedFile::fake()->image('primera.jpg')]);
        $producto = Producto::query()->firstOrFail();
        $oldPaths = [$producto->foto_path, $producto->foto_miniatura_path];

        $this->put(route('admin.productos.update', $producto), [...$data, 'foto' => UploadedFile::fake()->image('nueva.png', 100, 50)])->assertSessionHasNoErrors();

        $producto->refresh();
        Storage::disk('public')->assertMissing($oldPaths);
        $image = getimagesizefromstring(Storage::disk('public')->get($producto->foto_path));
        $this->assertSame([100, 50, IMAGETYPE_WEBP], array_slice($image, 0, 3));
        $this->assertCount(2, Storage::disk('public')->allFiles());
    }

    public function test_editing_without_a_photo_keeps_it_and_explicit_removal_deletes_it(): void
    {
        $this->signIn();
        Storage::fake('public');
        $data = $this->data();
        $this->post(route('admin.productos.store'), [...$data, 'foto' => UploadedFile::fake()->image('foto.jpg')]);
        $producto = Producto::query()->firstOrFail();
        $oldPaths = [$producto->foto_path, $producto->foto_miniatura_path];

        $this->put(route('admin.productos.update', $producto), $data)->assertSessionHasNoErrors();

        $this->assertSame($oldPaths[0], $producto->fresh()->foto_path);
        Storage::disk('public')->assertExists($oldPaths);

        $this->put(route('admin.productos.update', $producto), [...$data, 'quitar_foto' => '1'])->assertSessionHasNoErrors();

        $this->assertNull($producto->fresh()->foto_path);
        $this->assertNull($producto->fresh()->foto_miniatura_path);
        Storage::disk('public')->assertMissing($oldPaths);
    }

    public function test_deleting_an_unused_product_removes_both_photo_files(): void
    {
        $this->signIn();
        Storage::fake('public');
        $this->post(route('admin.productos.store'), [...$this->data(), 'foto' => UploadedFile::fake()->image('foto.jpg')]);
        $producto = Producto::query()->firstOrFail();
        $paths = [$producto->foto_path, $producto->foto_miniatura_path];

        $this->delete(route('admin.productos.destroy', $producto))->assertSessionHasNoErrors();

        $this->assertModelMissing($producto);
        Storage::disk('public')->assertMissing($paths);
    }

    public function test_svg_upload_is_rejected_without_saving_a_product_or_files(): void
    {
        $this->signIn();
        Storage::fake('public');

        $this->post(route('admin.productos.store'), [
            ...$this->data(), 'foto' => UploadedFile::fake()->createWithContent('foto.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ])->assertSessionHasErrors('foto');

        $this->assertDatabaseCount('productos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_oversized_upload_is_rejected(): void
    {
        $this->signIn();
        Storage::fake('public');

        $this->post(route('admin.productos.store'), [
            ...$this->data(), 'foto' => UploadedFile::fake()->image('foto.jpg')->size(4097),
        ])->assertSessionHasErrors(['foto' => 'La foto no debe superar los 4 MB.']);

        $this->assertDatabaseCount('productos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_excessive_dimensions_are_rejected_before_processing(): void
    {
        $this->signIn();
        Storage::fake('public');

        $this->post(route('admin.productos.store'), [
            ...$this->data(), 'foto' => UploadedFile::fake()->image('foto.jpg', 4001, 10),
        ])->assertSessionHasErrors(['foto' => 'La foto no debe superar los 4000 píxeles de ancho o alto.']);

        $this->assertDatabaseCount('productos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_database_failure_cleans_up_new_photo_files(): void
    {
        Storage::fake('public');
        $data = [...$this->data(), 'categoria_id' => 999999, 'foto' => UploadedFile::fake()->image('foto.jpg')];

        try {
            app(ProductoService::class)->create($data);
            $this->fail('La categoría inexistente debió impedir el registro.');
        } catch (QueryException) {
            $this->assertDatabaseCount('productos', 0);
        }

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_upload_requires_product_creation_permission(): void
    {
        $this->actingAs(Usuario::factory()->create());
        Storage::fake('public');

        $this->post(route('admin.productos.store'), [
            ...$this->data(), 'foto' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertForbidden();

        $this->assertDatabaseCount('productos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    private function signIn(): void
    {
        $usuario = Usuario::factory()->create();
        foreach (['productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar'] as $name) {
            $usuario->rol->permisos()->attach(Permiso::factory()->create(['nombre' => $name]));
        }
        $this->actingAs($usuario);
    }

    /** @return array{nombre: string, categoria_id: int, marca_id: int, estado: string} */
    private function data(): array
    {
        return [
            'nombre' => 'Aceite 10W-40',
            'categoria_id' => Categoria::factory()->create()->id,
            'marca_id' => Marca::factory()->create(['nombre' => 'Castrol'])->id,
            'estado' => '1',
        ];
    }
}
