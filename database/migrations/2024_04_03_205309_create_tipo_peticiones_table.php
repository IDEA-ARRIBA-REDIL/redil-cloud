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
        Schema::create('tipo_peticiones', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('nombre', 100);
            $table->smallinteger('orden');
            $table->text('json_versiculos')->nullable();
            $table->string('banner_email', 50)->nullable();
            $table->text('mensaje_parte_1')->nullable();
            $table->text('mensaje_parte_2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_peticiones');
    }
};
