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
            Schema::create('corte_materia_periodo', function (Blueprint $table) {
                $table->id();
                $table->foreignId('materia_periodo_id')->constrained('materia_periodo')->onDelete('cascade');
                $table->foreignId('corte_periodo_id')->constrained('cortes_periodo')->onDelete('cascade'); // Asumiendo que la tabla se llamaba cortes_periodo
                $table->decimal('porcentaje', 5, 2)->nullable();
                $table->boolean('cerrado')->default(false);
                $table->timestamps();
                $table->unique(['materia_periodo_id', 'corte_periodo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corte_materia_periodo');
    }
};
