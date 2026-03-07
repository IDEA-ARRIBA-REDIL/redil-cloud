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
        Schema::create('configuracion_rv', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_general');
            $table->string('nombre_metas', 50);
            $table->string('nombre_habitos', 50);
            $table->string('label_promedio_general', 50);
            $table->string('titulo_vista_final', 100);
            $table->string('label_btn_vista_final', 100);
            $table->string('url_vista_final', 100);
            $table->text('mensaje_vista_final');
            $table->integer('periodicidad')->default(30);
            $table->integer('promedio_general')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_rv');
    }
};
