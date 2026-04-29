<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')
                  ->constrained('sucursales')
                  ->cascadeOnDelete();
            $table->foreignId('item_id')
                  ->constrained('inventario_items')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->enum('tipo', ['entrada', 'salida', 'ajuste'])
                  ->default('entrada');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('stock_anterior', 10, 2);
            $table->decimal('stock_nuevo', 10, 2);
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->enum('motivo', [
                'compra', 'consumo', 'merma', 'descarte',
                'robo', 'devolucion', 'transferencia',
                'inventario_fisico', 'otro'
            ])->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};