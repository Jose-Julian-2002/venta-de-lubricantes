<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->unsignedInteger('usuario_id');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->restrictOnDelete();
            $table->foreignId('compra_detalle_id')->nullable()->constrained('compra_detalles')->restrictOnDelete();
            $table->string('tipo', 20);
            $table->unsignedInteger('cantidad');
            $table->unsignedInteger('stock_anterior');
            $table->unsignedInteger('stock_nuevo');
            $table->string('motivo', 100);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['producto_id', 'created_at', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movimientos');
    }
};
