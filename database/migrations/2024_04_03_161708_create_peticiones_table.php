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
        Schema::create('peticiones', function (Blueprint $table) {
          $table->id();
          $table->timestamps();
          $table->integer('user_id')->nullable(); // antes asistente_id
          //$table->integer('persona_externa_id')->nullable();
          $table->integer('tipo_peticion_id');
          $table->integer('estado'); // 1=Iniciada, 2=Finalizada, 3=Atendidas
          $table->text('descripcion');
          $table->text('respuesta')->nullable();
          $table->date('fecha');
          $table->integer('pais_id')->nullable();
          $table->integer('autor_creacion_id')->nullable();
          $table->integer('asignacion_peticion_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peticiones');
    }
};
