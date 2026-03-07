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
        Schema::create('tipo_aula', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',100);
            $table->boolean('sector')->default(false); // si sector esta true es sector, si es false es de templo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_aula');
    }
};
