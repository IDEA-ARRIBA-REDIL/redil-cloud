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
        Schema::create('productos_tienda', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('tipo', 50);
            $table->integer('costo_puntos');
            $table->integer('stock')->nullable();
            $table->integer('limite_por_usuario')->nullable();
            $table->string('imagen_ruta')->nullable();
            $table->text('instrucciones_canje')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos_tienda');
    }
};
