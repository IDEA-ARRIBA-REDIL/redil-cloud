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
        Schema::table('configuraciones', function (Blueprint $table): void {
            if (! Schema::hasColumn('configuraciones', 'mensaje_donacion_ofrenda')) {
                $table->text('mensaje_donacion_ofrenda')->nullable();
            }
            if (! Schema::hasColumn('configuraciones', 'banner_donacion_ofrenda')) {
                $table->string('banner_donacion_ofrenda')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table): void {
            $columns = [
                'mensaje_donacion_ofrenda',
                'banner_donacion_ofrenda',
            ];

            $existing = array_filter($columns, fn (string $col): bool => Schema::hasColumn('configuraciones', $col));
            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
