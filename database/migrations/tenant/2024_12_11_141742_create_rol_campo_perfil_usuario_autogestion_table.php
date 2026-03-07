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
        Schema::create('rol_campo_perfil_usuario_autogestion', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('campo_perfil_usuario_id');
            $table->integer('rol_id');
            $table->boolean('requerido')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rol_campo_perfil_usuario_autogestion');
    }
};
