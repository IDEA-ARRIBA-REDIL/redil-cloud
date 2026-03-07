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
        Schema::create('abono_categoria', function (Blueprint $table) {
            $table->id();
            $table->integer('abono_id');
            $table->integer('actividad_categoria_id');
            $table->integer('valor');
            $table->integer('moneda_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abono_categoria');
    }
};
