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
        Schema::create('intercesor_tipo_peticion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intercesor_id')->constrained('intercesores')->onDelete('cascade');
            $table->foreignId('tipo_peticion_id')->constrained('tipo_peticiones')->onDelete('cascade');
            $table->unique(['intercesor_id', 'tipo_peticion_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intercesor_tipo_peticion');
    }
};
