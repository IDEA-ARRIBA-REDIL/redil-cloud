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
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->string('instagram_user_id')->nullable();
            $table->string('instagram_username')->nullable();
            $table->text('instagram_access_token')->nullable();
            $table->timestamp('instagram_expires_at')->nullable();
            $table->timestamp('instagram_refreshed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->dropColumn(['instagram_user_id', 'instagram_username', 'instagram_access_token', 'instagram_expires_at', 'instagram_refreshed_at']);
        });
    }
};
