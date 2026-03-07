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
        Schema::create('consejeros', function (Blueprint $table) {
          $table->id();
          $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->unique();
          $table->boolean('activo')->default(true);
          $table->text('descripcion')->nullable();

          // Modalidades
          $table->boolean('atencion_presencial')->default(false);
          $table->string('direccion')->nullable();
          $table->boolean('atencion_virtual')->default(false);

          // Reglas de Agendamiento
          $table->unsignedSmallInteger('duracion_cita_minutos')->default(45);
          $table->unsignedSmallInteger('buffer_entre_citas_minutos')->default(15);
          $table->unsignedSmallInteger('dias_minimos_antelacion')->default(1);
          $table->unsignedSmallInteger('dias_maximos_futuro')->default(30);

          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejeros');
    }
};
