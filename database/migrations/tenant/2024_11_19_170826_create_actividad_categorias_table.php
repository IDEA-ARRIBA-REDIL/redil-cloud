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
        Schema::create('actividad_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->nullable();
            $table->integer('aforo')->nullable();
            $table->integer('actividad_id')->nullable();
            $table->timestamps();
            $table->integer('limite_compras')->default(1);
            $table->integer('aforo_ocupado')->nullable();
            $table->boolean('es_gratuita')->default(0);
            $table->integer('vinculacion_grupo')->nullable();
            $table->integer('actividad_grupo')->nullable();
            $table->integer('genero')->nullable();
            $table->integer('materia_periodo_id')->nullable();
            $table->integer('nivel_id')->nullable();
            $table->integer('limite_invitados')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_categorias');
    }
};
