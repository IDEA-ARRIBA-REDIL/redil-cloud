<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_plan_lector', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_id')->constrained('planes_lectores')->cascadeOnDelete();
            $table->foreignId('plan_lector_categoria_id')->constrained('plan_lector_categorias')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_plan_lector');
    }
};
