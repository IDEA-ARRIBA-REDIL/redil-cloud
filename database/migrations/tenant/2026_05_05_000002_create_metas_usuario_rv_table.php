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
        Schema::create('metas_usuario_rv', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rueda_de_la_vida_id');
            $table->unsignedBigInteger('seccion_rv_id');
            $table->string('nombre', 150);
            $table->timestamps();

            $table->foreign('rueda_de_la_vida_id')
                ->references('id')
                ->on('rueda_de_la_vida_user')
                ->onDelete('cascade');

            $table->foreign('seccion_rv_id')
                ->references('id')
                ->on('secciones_rv')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metas_usuario_rv');
    }
};
