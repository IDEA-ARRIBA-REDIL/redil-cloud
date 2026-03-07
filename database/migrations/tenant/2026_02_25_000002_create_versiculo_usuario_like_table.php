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
        Schema::create('versiculo_usuario_like', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('usuario_id')
                  ->constrained('users') // Asumiendo que tu tabla de usuarios es 'users'
                  ->onDelete('cascade');
                  
            $table->foreignId('versiculo_diario_id')
                  ->constrained('versiculos_diarios')
                  ->onDelete('cascade');

            $table->timestamps();

            // Evitar duplicados (un usuario solo puede dar un like por versículo)
            $table->unique(['usuario_id', 'versiculo_diario_id'], 'like_unico_usuario_versiculo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versiculo_usuario_like');
    }
};
