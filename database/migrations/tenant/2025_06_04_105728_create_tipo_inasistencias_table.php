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
        Schema::create('tipo_inasistencias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->boolean('observacion_obligatoria')->default(true); // antes muestra_modal_adicional
            $table->boolean('es_no_reporte')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_inasistencias');
    }
};
