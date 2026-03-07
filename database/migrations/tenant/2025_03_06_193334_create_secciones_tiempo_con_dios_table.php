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
        Schema::create('secciones_tiempo_con_dios', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('nombre', 200); //Este va ser para identificarlo en la plataforma
            $table->string('titulo_step', 100); // Esta titulo se muestra a lado de icono
            $table->string('titulo', 200); // Este se muestra dentro de contenido
            $table->string('subtitulo', 200); // Este se muestra tambien dentro del contenido
            $table->smallInteger('orden');
            $table->string('icono', 200)->nullable(); // Esta es un icono que va a lado de titulo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secciones_tiempo_con_dios');
    }
};
