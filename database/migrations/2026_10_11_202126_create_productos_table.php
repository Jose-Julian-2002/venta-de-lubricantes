<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedBigInteger('marca_id');
            $table->string('nombre', 150);
            $table->unsignedInteger('stock_actual')->default(0);
            $table->decimal('precio_compra_referencia', 10, 2);
            $table->decimal('precio_venta_menor', 10, 2);
            $table->decimal('precio_venta_mayor', 10, 2);
            $table->boolean('estado')->default(true);
            $table->foreign('categoria_id')->references('id')->on('categorias')->restrictOnDelete();
            $table->foreign('marca_id')->references('id')->on('marcas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
