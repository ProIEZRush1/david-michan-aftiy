<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('precio'); // stored in cents
            $table->text('descripcion')->nullable();
            $table->string('datos')->nullable();    // p.ej. "5 GB", "Datos ilimitados"
            $table->string('minutos')->nullable();  // p.ej. "Minutos ilimitados", "500 min"
            $table->string('sms')->nullable();       // p.ej. "SMS ilimitados"
            $table->integer('vigencia_dias')->default(30); // duración del plan en días
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
