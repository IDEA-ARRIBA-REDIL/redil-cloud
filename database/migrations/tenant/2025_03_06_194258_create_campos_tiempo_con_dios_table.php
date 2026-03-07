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
        Schema::create('campos_tiempo_con_dios', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('tipo_campo_tiempo_con_dios_id');
            $table->integer('seccion_tiempo_con_dios_id');
            $table->string('nombre', 100); // Como se identifica internamente

            $table->string('titulo', 100)->nullable(); // Este en el label cuando es de tipo texto
            $table->string('name_id', 100)->nullable();
            $table->string('placeholder', 100)->nullable()->nullable();
            $table->boolean('requerido')->default(0)->nullable();
            $table->string('class',200)->default('col-12')->nullable();
            $table->string('informacion_de_apoyo')->nullable()->nullable();

            $table->string('url_imagen')->nullable();
            $table->text('html')->nullable();
            $table->smallInteger('orden')->default(0)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campos_tiempo_con_dios');
    }
};
