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
      Schema::create('banner_generales', function (Blueprint $table) {
        $table->id();
        $table->string('imagen');
        $table->string('nombre')->nullable();
        $table->string('fecha_inicio')->nullable();
        $table->string('fecha_fin')->nullable();
        $table->text('link')->nullable();
        $table->boolean('visible');
        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::dropIfExists('banner_generales');
    }
};
