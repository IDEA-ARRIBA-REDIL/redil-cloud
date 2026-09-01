<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para almacenar likes únicos de feligreses a hitos.
     */
    public function up(): void
    {
        Schema::create('hito_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hito_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['hito_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hito_likes');
    }
};
