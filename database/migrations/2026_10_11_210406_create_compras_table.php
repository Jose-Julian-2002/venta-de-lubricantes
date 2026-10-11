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
        if (Schema::hasTable('compras')) {
            Schema::table('compras', function (Blueprint $table): void {
                $table->unsignedInteger('usuario_id')->change();
            });
            if (! collect(Schema::getForeignKeys('compras'))->contains('name', 'compras_usuario_id_foreign')) {
                Schema::table('compras', function (Blueprint $table): void {
                    $table->foreign('usuario_id')->references('id')->on('usuarios')->restrictOnDelete();
                });
            }
            if (! Schema::hasIndex('compras', 'compras_fecha_compra_id_index')) {
                Schema::table('compras', function (Blueprint $table): void {
                    $table->index(['fecha_compra', 'id']);
                });
            }

            return;
        }

        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $table->unsignedInteger('usuario_id');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->restrictOnDelete();
            $table->decimal('total', 10, 2);
            $table->dateTime('fecha_compra');
            $table->index(['fecha_compra', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
