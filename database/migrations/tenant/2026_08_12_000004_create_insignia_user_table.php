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
        Schema::create('insignia_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('insignia_id')
                ->constrained('insignias')
                ->cascadeOnDelete();

            $table->integer('progreso_actual')->default(0);
            $table->boolean('completada')->default(false);
            $table->timestamp('obtenida_el')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'insignia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insignia_user');
    }
};
