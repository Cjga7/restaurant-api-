<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')
                  ->constrained('sucursales')
                  ->cascadeOnDelete();
            $table->foreignId('mesa_id')
                  ->nullable()
                  ->constrained('mesas')
                  ->nullOnDelete();
            $table->string('cliente_nombre');
            $table->string('cliente_telefono', 20);
            $table->string('cliente_email')->nullable();
            $table->integer('cantidad_personas');
            $table->dateTime('fecha_hora');
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'completada', 'no_asistio'])
                  ->default('pendiente');
            $table->text('notas')->nullable();
            $table->foreignId('registrada_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};