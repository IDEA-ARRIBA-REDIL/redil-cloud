<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->unsignedInteger('max_miembros')->nullable()->comment('NULL = sin límite');
            $table->boolean('incluye_logo')->default(false)->comment('Personalización de logo e imagen de login');
            $table->boolean('incluye_marca_blanca')->default(false)->comment('Dominio propio + branding completo');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
