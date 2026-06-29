<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventario de números telefónicos disponibles para asignar a los pedidos.
        Schema::create('numeros', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();   // número telefónico (10 dígitos)
            $table->string('lada')->nullable();    // clave LADA / ciudad
            $table->string('tipo')->default('nuevo'); // nuevo | portabilidad
            // disponible → reservado → asignado
            $table->string('estado')->default('disponible');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numeros');
    }
};
