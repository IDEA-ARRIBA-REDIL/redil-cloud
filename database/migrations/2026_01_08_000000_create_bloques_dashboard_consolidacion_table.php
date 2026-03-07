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
        Schema::create('bloques_dashboard_consolidacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('bloque_dashboard_consolidacion_sede', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bloque_id')->constrained('bloques_dashboard_consolidacion')->onDelete('cascade');
            $table->foreignId('sede_id')->unique()->constrained('sedes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloque_dashboard_consolidacion_sede');
        Schema::dropIfExists('bloques_dashboard_consolidacion');
    }
};
