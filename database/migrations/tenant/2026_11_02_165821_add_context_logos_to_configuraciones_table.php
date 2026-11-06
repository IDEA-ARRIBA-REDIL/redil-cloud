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
            $table->string('logo_login')->nullable();
            $table->string('logo_menu_general')->nullable();
            $table->string('logo_menu_escuelas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table): void {
            $table->dropColumn(['logo_login', 'logo_menu_general', 'logo_menu_escuelas']);
        });
    }
};
