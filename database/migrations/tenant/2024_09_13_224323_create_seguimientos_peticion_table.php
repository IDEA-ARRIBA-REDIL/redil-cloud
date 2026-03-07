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
        Schema::create('seguimientos_peticion', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('peticion_id');
            $table->integer('usuario_id'); // Este es usuario creación
            $table->text('descripcion')->nullable();
            $table->date('fecha')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimientos_peticion');
    }
};
