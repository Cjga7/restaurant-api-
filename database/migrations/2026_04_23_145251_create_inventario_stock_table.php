<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')
                  ->constrained('sucursales')
                  ->cascadeOnDelete();
            $table->foreignId('item_id')
                  ->constrained('inventario_items')
                  ->cascadeOnDelete();
            $table->decimal('stock_actual', 10, 2)->default(0);
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->decimal('stock_ideal', 10, 2)->nullable();
            $table->decimal('precio_compra', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['sucursal_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_stock');
    }
};