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
        Schema::create('meta_rueda_de_la_vida', function (Blueprint $table) {
            $table->id();
            $table->integer('metas_id');
            $table->text('valor')->nullable();
            $table->integer('rueda_de_la_vida_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_rueda_de_la_vida');
    }
};
