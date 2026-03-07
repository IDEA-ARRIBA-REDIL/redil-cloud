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
        Schema::create('bitacora_tipos_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos')->onDelete('cascade');
            $table->foreignId('tipo_grupo_id_anterior')->nullable()->constrained('tipo_grupos')->onDelete('cascade');
            $table->foreignId('tipo_grupo_id_nuevo')->constrained('tipo_grupos')->onDelete('cascade');
            $table->foreignId('autor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora_tipos_grupo');
    }
};
