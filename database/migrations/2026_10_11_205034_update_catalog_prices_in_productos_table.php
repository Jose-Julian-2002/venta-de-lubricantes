<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->dropColumn('precio_compra_referencia');
            $table->decimal('precio_venta_menor', 10, 2)->nullable()->change();
            $table->decimal('precio_venta_mayor', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('productos')->whereNull('precio_venta_menor')->update(['precio_venta_menor' => 0]);
        DB::table('productos')->whereNull('precio_venta_mayor')->update(['precio_venta_mayor' => 0]);

        Schema::table('productos', function (Blueprint $table): void {
            $table->decimal('precio_compra_referencia', 10, 2)->default(0);
            $table->decimal('precio_venta_menor', 10, 2)->nullable(false)->change();
            $table->decimal('precio_venta_mayor', 10, 2)->nullable(false)->change();
        });
    }
};
