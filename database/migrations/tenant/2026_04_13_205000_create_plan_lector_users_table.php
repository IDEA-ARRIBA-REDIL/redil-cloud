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
        Schema::create('plan_lector_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_id')->constrained('planes_lectores')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Estado de la inscripción (inscrito, completado)
            $table->string('estado')->default('inscrito');
            
            $table->dateTime('fecha_inscripcion');
            $table->integer('porcentaje_progreso')->default(0);
            $table->integer('calificacion_usuario')->nullable()->after('porcentaje_progreso');
            
            $table->timestamps();

            // Evitar que un usuario se inscriba doble al mismo plan
            $table->unique(['plan_lector_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_lector_users');
    }
};
