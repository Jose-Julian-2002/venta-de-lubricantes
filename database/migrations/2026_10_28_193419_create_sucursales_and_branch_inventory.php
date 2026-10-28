<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursales', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->boolean('estado')->default(true);
        });

        DB::table('sucursales')->insert(['id' => 1, 'nombre' => 'Principal', 'estado' => true]);

        foreach (['usuarios', 'compras', 'ventas', 'stock_movimientos'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->foreignId('sucursal_id')->default(1)->constrained('sucursales')->restrictOnDelete();
            });
        }

        Schema::create('sucursal_productos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->restrictOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->unsignedInteger('stock_actual')->default(0);
            $table->unique(['sucursal_id', 'producto_id']);
        });

        DB::table('productos')->select('id', 'stock_actual')->orderBy('id')->chunkById(500, function ($productos): void {
            DB::table('sucursal_productos')->insert($productos->map(fn ($producto): array => [
                'sucursal_id' => 1, 'producto_id' => $producto->id, 'stock_actual' => $producto->stock_actual,
            ])->all());
        });

        Schema::table('ventas', function (Blueprint $table): void {
            $table->index(['sucursal_id', 'created_at']);
        });
        Schema::table('compras', function (Blueprint $table): void {
            $table->index(['sucursal_id', 'fecha_compra']);
        });
        Schema::table('stock_movimientos', function (Blueprint $table): void {
            $table->index(['sucursal_id', 'producto_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_movimientos', function (Blueprint $table): void {
            $table->dropIndex(['sucursal_id', 'producto_id', 'id']);
        });
        Schema::table('compras', function (Blueprint $table): void {
            $table->dropIndex(['sucursal_id', 'fecha_compra']);
        });
        Schema::table('ventas', function (Blueprint $table): void {
            $table->dropIndex(['sucursal_id', 'created_at']);
        });
        Schema::dropIfExists('sucursal_productos');
        Schema::withoutForeignKeyConstraints(function (): void {
            foreach (['stock_movimientos', 'ventas', 'compras', 'usuarios'] as $name) {
                Schema::table($name, function (Blueprint $table): void {
                    $table->dropConstrainedForeignId('sucursal_id');
                });
            }
        });
        Schema::dropIfExists('sucursales');
    }
};
