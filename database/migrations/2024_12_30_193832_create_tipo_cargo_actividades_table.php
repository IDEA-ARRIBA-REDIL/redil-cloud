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
        Schema::create('tipo_cargo_actividades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('descripcion', 500);
            $table->boolean('pestana_general')->default(false);
            $table->boolean('pestana_categorias')->default(false);
            $table->boolean('pestana_categorias_escuelas')->default(false);
            $table->boolean('pestana_abonos')->default(false);
            $table->boolean('pestana_encargados')->default(false);
            $table->boolean('pestana_asistencias')->default(false);
            $table->boolean('pestana_multimedia')->default(false);
            $table->boolean('pestana_formulario')->default(false);
            $table->boolean('opcion_activar_inactivar')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_cargo_actividades');
    }
};
