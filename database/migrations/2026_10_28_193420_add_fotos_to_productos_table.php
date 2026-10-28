<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->string('foto_path')->nullable();
            $table->string('foto_miniatura_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->dropColumn(['foto_path', 'foto_miniatura_path']);
        });
    }
};
