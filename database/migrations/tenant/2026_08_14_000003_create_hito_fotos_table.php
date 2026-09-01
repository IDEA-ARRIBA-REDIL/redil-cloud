<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para almacenar fotos del hito (fotos oficiales de admin o fotos aportadas por el feligrés).
     */
    public function up(): void
    {
        Schema::create('hito_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hito_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')
                ->comment('null = foto general subida por admin; con user_id = foto personal del usuario');
            $table->string('ruta', 255);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('es_admin')->default(false);
            $table->boolean('aprobada')->default(true);
            $table->timestamps();

            $table->index(['hito_id', 'es_admin']);
            $table->index(['hito_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hito_fotos');
    }
};
