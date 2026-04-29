<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_producto_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('menu_productos')
                  ->cascadeOnDelete();
            $table->foreignId('sucursal_id')
                  ->constrained('sucursales')
                  ->cascadeOnDelete();
            $table->decimal('precio', 10, 2)->nullable(); // null = usa precio_base
            $table->boolean('disponible')->default(true);
            $table->timestamps();

            $table->unique(['producto_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_producto_sucursal');
    }
};