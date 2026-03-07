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
        Schema::create('actividad_campos_adicionales_compra', function (Blueprint $table) {
            $table->id();
            $table->integer('actividad_id');
            $table->integer('compra_id');
            $table->integer('campo_adicional_id');
            $table->string('respuesta', 100);
            $table->integer('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_campos_adicionales_compra');
    }
};
