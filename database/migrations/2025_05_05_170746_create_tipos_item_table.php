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
        Schema::create('tipos_item', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('nombre', 100)->unique(); // e.g., "Taller", "Examen Parcial", "Quiz", "Exposición"
            $table->text('descripcion')->nullable(); // Optional description
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_item');
    }
};
