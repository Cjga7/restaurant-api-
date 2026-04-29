<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')
                  ->constrained('menu_categorias')
                  ->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->decimal('precio_base', 10, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_productos');
    }
};