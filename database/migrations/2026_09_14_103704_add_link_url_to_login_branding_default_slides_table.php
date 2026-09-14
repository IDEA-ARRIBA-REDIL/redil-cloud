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
        Schema::table('login_branding_default_slides', function (Blueprint $table) {
            $table->string('link_url', 2048)->nullable()->after('alt_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_branding_default_slides', function (Blueprint $table) {
            $table->dropColumn('link_url');
        });
    }
};
