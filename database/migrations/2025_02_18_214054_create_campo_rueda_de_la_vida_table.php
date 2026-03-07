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
        Schema::create('campo_rueda_de_la_vida', function (Blueprint $table) {
            $table->id();
            $table->integer('campos_seccion_rv_id');
            $table->double('valor');
            $table->integer('rueda_de_la_vida_id');
            $table->string('nombre_campo_abierto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campo_rueda_de_la_vida');
    }
};
