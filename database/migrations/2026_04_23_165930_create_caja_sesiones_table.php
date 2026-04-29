<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')
                  ->constrained('sucursales')
                  ->cascadeOnDelete();
            $table->foreignId('cajero_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_inicial', 10, 2);       // efectivo con que se abrió
            $table->decimal('monto_esperado', 10, 2)->nullable();  // calculado al cerrar
            $table->decimal('monto_real', 10, 2)->nullable();      // contado físicamente
            $table->decimal('diferencia', 10, 2)->nullable();      // real - esperado
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->text('notas_apertura')->nullable();
            $table->text('notas_cierre')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_sesiones');
    }
};