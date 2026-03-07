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
        Schema::create('campo_extra_rol_autogestion', function (Blueprint $table) {
            $table->id();
            $table->integer('rol_id');
            $table->integer('campo_extra_id');
            $table->boolean('requerido')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campo_extra_rol_autogestion');
    }
};
