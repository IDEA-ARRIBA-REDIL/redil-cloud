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
         // Si necesitas eliminar una tabla 'cortes_periodo' preexistente:
         // Schema::dropIfExists('cortes_periodo');

        Schema::create('cortes_periodo', function (Blueprint $table) {
            $table->id();
            // Foreign key al periodo al que pertenece esta instancia de corte
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            // Foreign key a la plantilla de corte de la escuela
            $table->foreignId('corte_escuela_id')->constrained('cortes_escuela')->onDelete('cascade');
            $table->date('fecha_inicio')->nullable(); // Fecha específica para este corte en este periodo
            $table->date('fecha_fin')->nullable(); // Fecha específica para este corte en este periodo
            $table->decimal('porcentaje', 5, 2)->nullable(); // Porcentaje específico para este corte en este periodo (ej: 30.00)
            $table->boolean('cerrado')->default(false); // Indica si el corte ya está cerrado para calificaciones
            $table->timestamps();

            // Unicidad: No puede haber dos instancias del mismo corte_escuela para el mismo periodo
            $table->unique(['periodo_id', 'corte_escuela_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortes_periodo');
    }
};