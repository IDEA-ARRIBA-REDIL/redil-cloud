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
        if (! Schema::hasColumn('reporte_bajas_altas', 'autor_id')) {
            Schema::table('reporte_bajas_altas', function (Blueprint $table): void {
                $table->foreignId('autor_id')->nullable()->constrained('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('reporte_bajas_altas', 'autor_id')) {
            Schema::table('reporte_bajas_altas', function (Blueprint $table): void {
                $table->dropForeign(['autor_id']);
                $table->dropColumn('autor_id');
            });
        }
    }
};
