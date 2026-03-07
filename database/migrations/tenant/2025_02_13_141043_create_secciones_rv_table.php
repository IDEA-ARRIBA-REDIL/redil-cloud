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
        Schema::create('secciones_rv', function (Blueprint $table) {
            $table->id();
            $table->string('titulo_barra', 50);
            $table->integer('tipo_seccion_id');
            $table->string('icono', 100);
            $table->integer('orden');
            $table->string('titulo_steper', 100);
            $table->string('nombre_seccion', 100);
            $table->text('subtitulo_seccion', 100)->nullable();
            $table->text('descripcion');
            $table->string('label_btn_bienvenida', 100)->nullable();
            $table->string('label_indice_promedio', 100)->nullable();
            $table->string('label_superior_atras', 100);
            $table->string('label_superior_adelante', 100);
            $table->string('label_btn_inferior_adelante', 100);
            $table->string('label_btn_inferior_atras', 100);
            $table->integer('min')->default(0);
            $table->integer('max')->default(10);
            $table->string('color',50)->nullable();
            $table->integer('promedio_minimo')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secciones_rv');
    }
};
