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
        Schema::table('stock_movimientos', function (Blueprint $table) {
            $table->foreignId('venta_detalle_id')->nullable()->constrained('venta_detalles')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movimientos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('venta_detalle_id');
        });
    }
};
