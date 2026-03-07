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
        Schema::create('opciones_elemento_formulario_actividad', function (Blueprint $table) {
            $table->id();
            $table->integer('elemento_formulario_actividad_id')->nullable();
            $table->string('valor_texto')->nullable();
            $table->integer('valor_entero')->nullable();
            $table->text('valor_text_area')->nullable();
            $table->date('valor_fecha')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opciones_elemento_formulario_actividad');
    }
};
