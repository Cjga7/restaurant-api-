<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')
                  ->constrained('pedidos')
                  ->cascadeOnDelete();
            $table->foreignId('caja_sesion_id')
                  ->nullable()
                  ->constrained('caja_sesiones')
                  ->nullOnDelete();
            $table->foreignId('cajero_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->enum('metodo', ['efectivo', 'tarjeta', 'qr', 'transferencia', 'mixto']);
            $table->decimal('monto_total', 10, 2);
            $table->decimal('monto_efectivo', 10, 2)->default(0);
            $table->decimal('monto_tarjeta', 10, 2)->default(0);
            $table->decimal('monto_qr', 10, 2)->default(0);
            $table->decimal('monto_recibido', 10, 2)->nullable(); // para calcular cambio
            $table->decimal('cambio', 10, 2)->default(0);
            $table->string('referencia')->nullable();  // número de comprobante, tarjeta, etc.
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};