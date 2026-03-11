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
        Schema::create('curso_evaluacion_resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('curso_item_id')->constrained('curso_items')->onDelete('cascade');
            $table->foreignId('curso_evaluacion_id')->constrained('curso_evaluaciones')->onDelete('cascade');
            
            $table->decimal('nota', 5, 2)->comment('Nota o porcentaje obtenido (0-100)');
            $table->boolean('aprobado')->default(false);
            $table->integer('intento')->default(1);
            $table->json('respuestas_json')->nullable()->comment('Copia de seguridad de las respuestas marcadas');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_evaluacion_resultados');
    }
};
