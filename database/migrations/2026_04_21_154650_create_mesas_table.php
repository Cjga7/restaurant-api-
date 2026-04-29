<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')
                  ->constrained('sucursales')
                  ->cascadeOnDelete();
            $table->string('numero', 20);
            $table->integer('capacidad')->default(4);
            $table->enum('estado', ['disponible', 'ocupada', 'reservada', 'inactiva'])
                  ->default('disponible');
            $table->string('qr_code')->nullable();
            $table->string('ubicacion')->nullable(); // ej: "Terraza", "Interior"
            $table->timestamps();

            $table->unique(['sucursal_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};