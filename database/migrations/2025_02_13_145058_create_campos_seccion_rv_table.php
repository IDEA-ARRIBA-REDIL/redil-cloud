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
        Schema::create('campos_seccion_rv', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->boolean('abierto')->default(false);
            $table->integer('seccion_rv_id');
            $table->integer('orden');
            $table->string('color', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campos_seccion_rv');
    }
};
