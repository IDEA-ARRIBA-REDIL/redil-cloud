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
        Schema::create('reportes_grupo_bajas_altas', function (Blueprint $table) {
          $table->id();
          $table->timestamps();
          $table->string('motivo',100);
          $table->text('observaciones')->nullable();
          $table->date('fecha')->nullable();
          $table->integer('grupo_id');
          $table->boolean('dado_baja'); //0: dado de baja  1: dado de alta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes_grupo_bajas_altas');
    }
};
