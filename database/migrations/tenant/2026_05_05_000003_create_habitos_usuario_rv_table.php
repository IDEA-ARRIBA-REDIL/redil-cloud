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
        Schema::create('habitos_usuario_rv', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meta_usuario_rv_id');
            $table->string('nombre', 150);
            $table->timestamps();

            $table->foreign('meta_usuario_rv_id')
                ->references('id')
                ->on('metas_usuario_rv')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitos_usuario_rv');
    }
};
