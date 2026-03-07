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
        // antes la tabla se llamaba tipo_asistente_bloqueado_tipo_usuario
        Schema::create('tipo_usuario_bloqueado_rol', function (Blueprint $table) {
            $table->id();
            $table->integer('tipo_usuario_id'); // antes tipo_asistente_id
            $table->integer('rol_id'); // ante tipo_usuario_id
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_usuario_bloqueado_rol');
    }
};
