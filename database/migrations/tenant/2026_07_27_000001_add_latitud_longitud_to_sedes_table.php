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
        Schema::table('sedes', function (Blueprint $table) {
            if (! Schema::hasColumn('sedes', 'latitud')) {
                $table->string('latitud', 25)->nullable()->after('barrio_auxiliar');
            }
            if (! Schema::hasColumn('sedes', 'longitud')) {
                $table->string('longitud', 25)->nullable()->after('latitud');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sedes', function (Blueprint $table) {
            if (Schema::hasColumn('sedes', 'latitud')) {
                $table->dropColumn('latitud');
            }
            if (Schema::hasColumn('sedes', 'longitud')) {
                $table->dropColumn('longitud');
            }
        });
    }
};
