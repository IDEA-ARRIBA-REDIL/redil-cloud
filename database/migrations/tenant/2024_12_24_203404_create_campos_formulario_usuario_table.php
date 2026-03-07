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
        Schema::create('campos_formulario_usuario', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('nombre', 100);
            $table->string('name_id', 100);
            $table->string('placeholder', 100)->nullable();
            $table->string('nombre_bd', 100)->nullable();
            $table->boolean('tiene_descargable')->default(0);

            /* cuando es un campo extra */
            $table->boolean('es_campo_extra')->default(0);
            $table->integer('tipo_de_campo')->nullable();
            $table->text('opciones_select')->nullable();

            /**/
            $table->boolean('visible_resumen')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campos_formulario_usuario');
    }
};
