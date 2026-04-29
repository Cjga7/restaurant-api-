<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')
                  ->constrained('sucursales')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('ci', 20)->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_ingreso');
            $table->enum('cargo', ['gerente', 'cajero', 'mozo', 'cocinero', 'ayudante'])
                  ->default('mozo');
            $table->enum('turno', ['mañana', 'tarde', 'noche', 'completo'])
                  ->default('completo');
            $table->decimal('salario', 10, 2)->nullable();
            $table->string('foto')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};