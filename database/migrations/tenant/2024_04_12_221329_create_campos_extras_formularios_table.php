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
        Schema::create('campos_extras_formularios', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('campo_extra_id');
            $table->integer('formulario_id');
            $table->boolean('required')->nullable();
            $table->boolean('visible')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campos_extras_formularios');
    }
};
