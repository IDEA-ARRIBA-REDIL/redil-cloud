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
        Schema::table('materia_periodo', function (Blueprint $table) {
            $table->foreignId('nivel_id')->nullable()->constrained('niveles_escuelas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materia_periodo', function (Blueprint $table) {
            $table->dropForeign(['nivel_id']);
            $table->dropColumn('nivel_id');
        });
    }
};
