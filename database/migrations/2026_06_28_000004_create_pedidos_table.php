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
            $table->foreignId('bot_contact_id')->nullable()->constrained('bot_contacts')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();
            $table->foreignId('numero_id')->nullable()->constrained('numeros')->nullOnDelete();
            $table->string('cliente')->nullable();
            $table->string('telefono');
            $table->string('email')->nullable();
            // Flujo: iniciado → en_pago → pagado → numero_asignado → entregado (+ cancelado)
            $table->string('estado')->default('iniciado');
            $table->integer('total')->nullable();       // total en centavos
            $table->string('link_pago')->nullable();    // enlace de pago generado en el flujo
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
