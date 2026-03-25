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
        Schema::table('tipo_vinculaciones', function (Blueprint $table) {
            $table->boolean('viene_de_otra_iglesia')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_vinculaciones', function (Blueprint $table) {
            $table->dropColumn('viene_de_otra_iglesia');
        });
    }
};
