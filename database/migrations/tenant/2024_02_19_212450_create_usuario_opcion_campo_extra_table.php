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
       // antes se llamaba la tabla  asistente_opcion_campo_extra_id
        Schema::create('usuario_opcion_campo_extra', function (Blueprint $table) {
          $table->id();
          $table->integer('user_id');
          $table->integer('campo_extra_id');
          $table->text('valor')->nullable();
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_opcion_campo_extra');
    }
};
