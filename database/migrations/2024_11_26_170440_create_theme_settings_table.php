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
          Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('class')->nullable();  // Nombre de la variable (ej: 'blue', 'indigo', 'primary')
            $table->string('value')->nullable();  // Valor hexadecimal (ej: '#007bff')
            $table->string('category')->nullable(); // Categoría (ej: 'colors', 'buttons', 'text')
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('gradient')->default(false);
            $table->string('value2')->default('#f0f8ff00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
