<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('estados_civiles', function (Blueprint $table) {
            $table->boolean('es_matrimonio')->default(false)->after('es_union_libre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estados_civiles', function (Blueprint $table) {
            $table->dropColumn('es_matrimonio');
        });
    }
};
