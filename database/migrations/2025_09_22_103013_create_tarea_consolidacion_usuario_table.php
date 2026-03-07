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
        Schema::create('tarea_consolidacion_usuario', function (Blueprint $table) {
            $table->id();
            $table->integer('tarea_consolidacion_id');
            $table->integer('user_id');
            $table->smallInteger('estado_tarea_consolidacion_id'); // antes estado
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarea_consolidacion_usuario');
    }
};
