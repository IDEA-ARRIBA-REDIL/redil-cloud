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
        Schema::create('actividad_carritos_compra', function (Blueprint $table) {
            $table->id();
            $table->integer('actividad_id');
            $table->integer('actividad_categoria_id');
            $table->integer('compra_id');
            $table->integer('cantidad');
            $table->double('precio');
            $table->integer('user_id')->nullable();
            $table->integer('pago_id')->nullable();
            $table->integer('inscripcion_id')->nullable();
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_carritos_compra');
    }
};
