<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_lector_contenidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_dia_id')->constrained('plan_lector_dias')->cascadeOnDelete();
            $table->integer('orden')->default(0);
            $table->foreignId('plan_lector_tipo_contenido_id')->constrained('plan_lector_tipo_contenidos')->restrictOnDelete();
            $table->longText('contenido');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_lector_contenidos');
    }
};
