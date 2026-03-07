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
        Schema::create('monedas', function (Blueprint $table) {
          $table->id();
          $table->string('nombre',20);
          $table->string('nombre_corto',10);
          $table->double('donacion_maxima');
          $table->boolean('habilitada_donacion')->default('false')->nullable();
          $table->boolean('default')->default(0);
          $table->string('codigo_alpha',10)->defult('co');
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monedas');
    }
};
