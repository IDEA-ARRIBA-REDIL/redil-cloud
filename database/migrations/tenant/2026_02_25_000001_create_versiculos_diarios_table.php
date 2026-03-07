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
        Schema::create('versiculos_diarios', function (Blueprint $table) {
            $table->id();
            
            // Datos para la API
            $table->string('version_uri');     // Ej: /rv1960
            $table->string('libro_nombre');    // Ej: Juan
            $table->string('cita_referencia'); // Ej: 3:16
            
            // Contenido local (opcional, para guardar el texto del versículo)
            $table->text('texto_versiculo')->nullable(); 

            // Multimedia
            $table->string('ruta_imagen')->nullable();      // Ruta en el storage
            $table->string('url_video_reflexion')->nullable(); // YouTube/Vimeo
            
            // Programación
            $table->date('fecha_publicacion')->unique(); // El día que debe aparecer
            
            $table->foreignId('usuario_id')
                  ->constrained('users')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versiculos_diarios');
    }
};
