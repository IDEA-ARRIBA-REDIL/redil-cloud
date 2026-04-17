<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_lector_dias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_id')->constrained('planes_lectores')->cascadeOnDelete();
            $table->integer('dia');
            $table->string('titulo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_lector_dias');
    }
};
