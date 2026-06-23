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
        Schema::create('informes_personalizados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('link', 100)->nullable();
            $table->boolean('activo')->default(false);
            $table->boolean('seleccione_dia_corte')->default(true);
            $table->boolean('clasificaciones')->default(true);
            $table->boolean('visible_solo_administradores')->default(false);
            $table->boolean('informe_numerico')->default(false);
            $table->boolean('add_id_a_la_url')->default(false);
            $table->string('nombre_boton', 50)->default('Ver');
            $table->unsignedBigInteger('tipo_informe_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes_personalizados');
    }
};
