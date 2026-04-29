<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_items', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('categoria', ['ingrediente', 'bebida', 'empaque', 'otro'])
                  ->default('ingrediente');
            $table->enum('unidad', ['kg', 'g', 'litro', 'ml', 'unidad', 'caja', 'paquete', 'botella', 'lata'])
                  ->default('unidad');
            $table->foreignId('producto_menu_id')
                  ->nullable()
                  ->constrained('menu_productos')
                  ->nullOnDelete(); // vincula bebida de inventario con producto del menú
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_items');
    }
};