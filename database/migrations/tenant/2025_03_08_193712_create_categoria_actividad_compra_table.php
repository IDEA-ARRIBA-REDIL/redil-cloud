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
        Schema::create('categoria_actividad_compra', function (Blueprint $table) {
            $table->id();
            $table->integer('compra_id');
            $table->integer('actividad_categoria_id');
            $table->double('valor');
            $table->integer('cantidad');
            $table->integer('moneda_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_actividad_compra');
    }
};
