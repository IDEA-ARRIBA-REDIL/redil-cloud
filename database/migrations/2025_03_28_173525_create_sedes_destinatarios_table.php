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
        Schema::create('sedes_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('barrio', 50);
            $table->string('direccion', 200);
            $table->decimal('latitud', 10, 8);  // Ej: 4.60971000
            $table->decimal('longitud', 11, 8); // Ej: -74.08175000
            $table->text('detalle')->nullable();
            $table->timestamps();
            
            // Index para búsquedas
            $table->index('barrio');
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sedes_destinatarios');
    }
};
