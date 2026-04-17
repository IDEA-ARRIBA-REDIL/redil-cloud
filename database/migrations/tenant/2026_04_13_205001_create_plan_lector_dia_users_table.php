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
        Schema::create('plan_lector_dia_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_dia_id')->constrained('plan_lector_dias')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->dateTime('fecha_completado')->useCurrent();
            
            $table->timestamps();

            // Evitar duplicados
            $table->unique(['plan_lector_dia_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_lector_dia_users');
    }
};
