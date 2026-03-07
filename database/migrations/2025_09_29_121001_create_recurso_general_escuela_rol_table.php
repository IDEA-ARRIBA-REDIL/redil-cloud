<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurso_general_escuela_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurso_general_escuela_id')->constrained('recursos_generales_escuela')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurso_general_escuela_rol');
    }
};
