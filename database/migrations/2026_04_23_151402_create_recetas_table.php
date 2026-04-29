<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('menu_productos')
                  ->cascadeOnDelete();
            $table->foreignId('item_id')
                  ->constrained('inventario_items')
                  ->cascadeOnDelete();
            $table->decimal('cantidad', 10, 3); // soporta 3 decimales (ej: 0.150 kg)
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['producto_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};