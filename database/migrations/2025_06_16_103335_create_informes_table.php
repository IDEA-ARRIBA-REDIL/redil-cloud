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
      // Esta es la tabla que guarda la información principal de los diferentes informes
        Schema::create('informes', function (Blueprint $table) {
          $table->id();
          $table->string('nombre', 100)->nullable();
          $table->text('descripcion')->nullable();
          $table->string('link', 100)->nullable();
          $table->boolean('activo')->nullable();
          $table->boolean('seleccione_dia_corte')->nullable()->default(true);
          $table->boolean('clasificaciones')->nullable()->default(true);
          $table->boolean('visible_solo_administradores')->nullable()->default(false);
          $table->boolean('informe_numerico')->nullable()->default(false);
          $table->boolean('add_id_a_la_url')->nullable()->default(false);
          $table->string('nombre_boton', 50)->nullable()->default('Ver');
          $table->integer('tipo_informe_id')->nullable();
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes');
    }
};
