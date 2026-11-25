<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peticiones', function (Blueprint $table) {
            if (! Schema::hasColumn('peticiones', 'desea_pertenecer_iglesia')) {
                $table->boolean('desea_pertenecer_iglesia')->nullable()->after('ciudad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('peticiones', function (Blueprint $table) {
            if (Schema::hasColumn('peticiones', 'desea_pertenecer_iglesia')) {
                $table->dropColumn('desea_pertenecer_iglesia');
            }
        });
    }
};
