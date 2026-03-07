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
      // antes se llamaba la tabla grupo_opcion_campo_extra_id
        Schema::create('grupo_opcion_campo_extra', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('campo_extra_grupo_id');
            $table->integer('grupo_id');
            $table->text('valor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_opcion_campo_extra');
    }
};
