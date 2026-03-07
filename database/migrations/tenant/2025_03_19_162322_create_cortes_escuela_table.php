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
            Schema::create('cortes_escuela', function (Blueprint $table) {
                $table->id();
                // Foreign key a la escuela a la que pertenece esta plantilla de corte
                $table->foreignId('escuela_id')->constrained('escuelas')->onDelete('cascade');
                $table->string('nombre', 100); // Ejemplo: "Corte 1", "Primer Trimestre", "Evaluación Final"
                $table->integer('porcentaje')->nullable();
                $table->unsignedTinyInteger('orden')->default(0); // Para mantener un orden específico si es necesario
                $table->timestamps();

                // Unicidad: No puede haber dos cortes con el mismo nombre y orden para la misma escuela
                
               
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortes_escuela');
    }
};
