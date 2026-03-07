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
        Schema::create('paso_crecimiento_tipo_consejeria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paso_crecimiento_id')->constrained('pasos_crecimiento')->onDelete('cascade');
            $table->foreignId('tipo_consejeria_id')->constrained('tipo_consejerias')->onDelete('cascade');
            $table->unique(['paso_crecimiento_id', 'tipo_consejeria_id'], 'paso_tipo_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paso_crecimiento_tipo_consejeria');
    }
};
