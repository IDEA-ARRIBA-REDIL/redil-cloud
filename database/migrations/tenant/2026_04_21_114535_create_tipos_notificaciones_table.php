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
        Schema::create('tipos_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('modulo');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('alcance')->default('individual'); // global, individual, escala_ministerial, ministerio_directo
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_notificaciones');
    }
};
