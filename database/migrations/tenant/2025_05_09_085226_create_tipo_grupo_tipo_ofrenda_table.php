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
        Schema::create('tipo_grupo_tipo_ofrenda', function (Blueprint $table) {
            $table->id();
            $table->integer('tipo_grupo_id');
            $table->integer('tipo_ofrenda_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_grupo_tipo_ofrenda');
    }
};
