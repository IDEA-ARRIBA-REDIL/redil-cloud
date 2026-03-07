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
        Schema::create('parientes_usuarios', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('pariente_user_id')->nullable();
            $table->boolean('es_el_responsable')->default(false);
            $table->boolean('acepto_terminos_condiciones')->default(false);
            $table->integer('tipo_pariente_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parientes_usuarios');
    }
};
