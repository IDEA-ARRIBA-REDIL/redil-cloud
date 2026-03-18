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
        Schema::table('pasos_crecimiento', function (Blueprint $table) {
            $table->boolean('habilitada_consolidacion')->default(false)->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasos_crecimiento', function (Blueprint $table) {
            $table->dropColumn('habilitada_consolidacion');
        });
    }
};
