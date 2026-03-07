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
        Schema::create('consejero_tipo_consejeria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consejero_id')->constrained('consejeros')->onDelete('cascade');
            $table->foreignId('tipo_consejeria_id')->constrained('tipo_consejerias')->onDelete('cascade');

            // Opcional: Evita duplicados
            $table->unique(['consejero_id', 'tipo_consejeria_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejero_tipo_consejeria');
    }
};
