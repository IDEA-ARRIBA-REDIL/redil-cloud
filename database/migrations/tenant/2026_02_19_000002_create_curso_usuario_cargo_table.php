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
        Schema::create('curso_usuario_cargo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tipo_cargo_curso_id')->constrained('tipos_cargo_cursos')->cascadeOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Evitar duplicados del mismo usuario en el mismo curso con el mismo rol
            $table->unique(['curso_id', 'usuario_id', 'tipo_cargo_curso_id'], 'curso_user_cargo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_usuario_cargo');
    }
};
