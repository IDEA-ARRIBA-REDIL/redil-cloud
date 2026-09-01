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
        Schema::table('escuelas', function (Blueprint $table) {
            if (! Schema::hasColumn('escuelas', 'caracter_obligatorio')) {
                $table->boolean('caracter_obligatorio')->default(false)->after('descripcion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escuelas', function (Blueprint $table) {
            if (Schema::hasColumn('escuelas', 'caracter_obligatorio')) {
                $table->dropColumn('caracter_obligatorio');
            }
        });
    }
};
