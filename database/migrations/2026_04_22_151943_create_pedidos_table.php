<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')
                  ->constrained('sucursales')
                  ->cascadeOnDelete();
            $table->foreignId('mesa_id')
                  ->nullable()
                  ->constrained('mesas')
                  ->nullOnDelete();
            $table->foreignId('mozo_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('numero', 20); // N° correlativo de pedido
            $table->enum('tipo', ['mesa', 'delivery', 'llevar'])->default('mesa');
            $table->enum('estado', [
                'abierto', 'enviado', 'en_preparacion', 'listo',
                'entregado', 'pagado', 'cancelado'
            ])->default('abierto');
            $table->string('cliente_nombre')->nullable();  // para delivery/llevar
            $table->string('cliente_telefono', 20)->nullable();
            $table->string('cliente_direccion')->nullable(); // para delivery
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};