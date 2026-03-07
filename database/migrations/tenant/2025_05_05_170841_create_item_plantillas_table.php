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
        Schema::create('item_plantillas', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key to the Materia this template belongs to
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');

            // Foreign key to the CorteEscuela this template item is associated with
            $table->foreignId('corte_escuela_id')->constrained('cortes_escuela')->onDelete('cascade');

            // (Optional) Foreign key to the type of item
            $table->foreignId('tipo_item_id')->nullable()->constrained('tipos_item')->onDelete('set null');

            $table->string('nombre', 255); // Name of the item template (e.g., "Taller 1", "Examen Final")
            $table->text('contenido')->nullable(); // Description or instructions for the item
            $table->boolean('visible_predeterminado')->default(true); // Default visibility state for instances
            $table->boolean('entregable_predeterminado')->default(false); // Default setting for requiring submission
            $table->decimal('porcentaje_sugerido', 5, 2)->nullable(); // Suggested percentage within the cut (e.g., 10.00 for 10%)
            $table->unsignedInteger('orden')->default(0); // To define the order of items within a cut/materia

            $table->timestamps(); // created_at, updated_at

            // Index for faster lookups
            $table->index(['materia_id', 'corte_escuela_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_plantillas');
    }
};
