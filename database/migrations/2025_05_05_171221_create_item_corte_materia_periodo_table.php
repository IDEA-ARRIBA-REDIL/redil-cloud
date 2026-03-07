<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Changed class name to reflect the new table name for clarity
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('item_corte_materia_periodo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_periodo_id')->constrained('materia_periodo')->onDelete('cascade');
            $table->foreignId('corte_periodo_id')->constrained('cortes_periodo')->onDelete('cascade');
            $table->foreignId('item_plantilla_id')->nullable()->constrained('item_plantillas')->onDelete('set null'); // Origen
            $table->foreignId('tipo_item_id')->nullable()->constrained('tipos_item')->onDelete('set null'); // Asumiendo tabla tipos_item

            // ¡IMPORTANTE! Vínculo con el horario específico
            $table->foreignId('horario_materia_periodo_id')->constrained('horarios_materia_periodo')->onDelete('cascade');

            $table->string('nombre', 500);
            $table->text('contenido')->nullable();
            $table->boolean('visible')->default(false);
            $table->date('fecha_inicio')->nullable(); // Se llenará desde CortePeriodo
            $table->date('fecha_fin')->nullable();   // Se llenará desde CortePeriodo
            $table->boolean('habilitar_entregable')->default(false);
            $table->decimal('porcentaje', 5, 2)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['materia_periodo_id', 'corte_periodo_id']);
            $table->index('horario_materia_periodo_id');
        });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Changed table name from 'item_instancias' to 'item_corte_materia_periodo'
        Schema::dropIfExists('item_corte_materia_periodo');
    }
};
