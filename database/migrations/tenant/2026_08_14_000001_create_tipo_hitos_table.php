<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crea la tabla de tipos de hito para soportar extensibilidad futura
     * (General, Automático, Actividad, Manual Individual, etc.).
     */
    public function up(): void
    {
        Schema::create('tipo_hitos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 50)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->string('icono', 50)->default('ti ti-award');
            $table->string('color', 20)->default('#7c5cfc');

            // Flags de funcionalidad y comportamiento
            $table->boolean('requiere_trigger')->default(false)
                ->comment('Indica si requiere configurar triggers de otros módulos');
            $table->boolean('requiere_actividad')->default(false)
                ->comment('Indica si se vincula a una actividad del módulo Actividades');
            $table->boolean('permite_fotos_usuario')->default(true)
                ->comment('Indica si por defecto los feligreses pueden aportar fotos');
            $table->boolean('permite_likes')->default(true)
                ->comment('Indica si permite recibir likes');
            $table->boolean('evaluacion_dinamica')->default(true)
                ->comment('true = calculado al vuelo por filtros/logros; false = requiere asignación individual');

            // Configuraciones extensibles
            $table->json('configuracion')->nullable()
                ->comment('Parámetros adicionales extensibles en formato JSON');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_hitos');
    }
};
