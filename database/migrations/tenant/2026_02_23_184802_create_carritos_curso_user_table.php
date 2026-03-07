<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carritos_curso_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Items guardados en formato JSON [{curso_id: 1, precio: 50000}, ...]
            $table->json('items')->nullable();

            // Total calculado del carrito
            $table->decimal('total', 12, 2)->default(0);

            // Estado del carrito (pendiente, completado, abandonado)
            $table->string('estado', 50)->default('pendiente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carritos_curso_user');
    }
};
