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
        Schema::create('reglas_gamificacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('accion_codigo', 100);
            $table->string('frecuencia', 50);
            $table->integer('meta_cantidad')->default(1);
            $table->integer('puntos_premio')->default(0);

            $table->foreignId('insignia_id')
                ->nullable()
                ->constrained('insignias')
                ->nullOnDelete();

            $table->integer('limite_diario')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglas_gamificacion');
    }
};
