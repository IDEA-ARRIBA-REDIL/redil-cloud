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
        // 1. Tipos de ítems (Lección, Evaluación, etc.)
        Schema::create('curso_item_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Video Lección", "Examen Final"
            $table->string('categoria'); // Ej: "leccion", "evaluacion"
            $table->string('codigo')->unique(); // Ej: "leccion", "evaluacion", "video", "lectura"
            $table->string('icono')->nullable(); // Ej: "fas fa-play"
            $table->timestamps();
        });

        // 2. Módulos del curso
        Schema::create('curso_modulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        // 3. Ítems del Módulo (El contenedor polimórfico)
        Schema::create('curso_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_modulo_id')->constrained('curso_modulos')->onDelete('cascade');
            $table->foreignId('curso_item_tipo_id')->constrained('curso_item_tipos');
            
            $table->string('titulo');
            $table->integer('orden')->default(0);

            // Columnas Polimórficas: itemable_id e itemable_type
            $table->morphs('itemable'); 

            $table->timestamps();
        });

        // 4. Contenido tipo Lección
        Schema::create('curso_lecciones', function (Blueprint $table) {
            $table->id();
            $table->longText('contenido_html')->nullable();
            $table->string('video_url')->nullable();
            $table->enum('video_plataforma', ['vimeo', 'youtube', 'custom'])->nullable();
            $table->string('archivo_path')->nullable(); // Para PDFs o recursos
            $table->longText('iframe_code')->nullable(); // Para contenidos embebidos (Canva, Genially, etc)
            $table->timestamps();
        });

        // 5. Contenido tipo Evaluación
        Schema::create('curso_evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('minimo_aprobacion')->default(50)->comment('Porcentaje de 0 a 100'); 
            $table->integer('limite_tiempo')->nullable()->comment('En minutos');
            $table->integer('cantidad_repeticiones')->default(0)->comment('0 es sin repetición, 1 es un intento extra, etc.');
            $table->integer('tiempo_dilatacion')->default(0)->comment('Tiempo de espera entre repeticiones en horas');
            $table->timestamps();
        });

        // 6. Preguntas de la evaluación
        Schema::create('curso_preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_evaluacion_id')->constrained('curso_evaluaciones')->onDelete('cascade');
            $table->text('pregunta');
            $table->enum('tipo_respuesta', ['unica', 'multiple', 'verdadero_falso']);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        // 7. Opciones de respuesta
        Schema::create('curso_pregunta_opciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_pregunta_id')->constrained('curso_preguntas')->onDelete('cascade');
            $table->text('opcion');
            $table->boolean('es_correcta')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_pregunta_opciones');
        Schema::dropIfExists('curso_preguntas');
        Schema::dropIfExists('curso_evaluaciones');
        Schema::dropIfExists('curso_lecciones');
        Schema::dropIfExists('curso_items');
        Schema::dropIfExists('curso_modulos');
        Schema::dropIfExists('curso_item_tipos');
    }
};
