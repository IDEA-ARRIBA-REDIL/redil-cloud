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
        Schema::create('tipos_seccion_rv', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->integer('min');
            $table->integer('max');
            $table->boolean('validacion')->default(false);
            $table->boolean('resumen')->default(false);
            $table->boolean('encuesta')->default(false);
            $table->string('url_imagen', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_seccion_rv');
    }
};
