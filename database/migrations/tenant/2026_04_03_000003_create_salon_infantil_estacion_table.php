<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_infantil_estacion', function (Blueprint $table) {
            $table->id();
            $table->integer('salon_infantil_id');
            $table->integer('estacion_salon_infantil_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_infantil_estacion');
    }
};
