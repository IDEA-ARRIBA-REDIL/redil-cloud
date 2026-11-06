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
        Schema::table('novedades_actividad', function (Blueprint $table) {
            $table->unsignedBigInteger('actividad_id')->nullable()->change();

            if (! Schema::hasColumn('novedades_actividad', 'sede_id')) {
                $table->foreignId('sede_id')
                    ->nullable()
                    ->after('actividad_id')
                    ->constrained('sedes')
                    ->onDelete('set null');
                $table->index('sede_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('novedades_actividad', function (Blueprint $table) {
            if (Schema::hasColumn('novedades_actividad', 'sede_id')) {
                $table->dropForeign(['sede_id']);
                $table->dropIndex(['sede_id']);
                $table->dropColumn('sede_id');
            }
        });
    }
};
