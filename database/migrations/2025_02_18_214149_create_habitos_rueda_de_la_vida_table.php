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
        Schema::create('habitos_rueda_de_la_vida', function (Blueprint $table) {
            $table->id();
            $table->integer('habitos_rueda_vida_id');
            $table->integer('rueda_de_la_vida_id');
            $table->text('valor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitos_rueda_de_la_vida');
    }
};
