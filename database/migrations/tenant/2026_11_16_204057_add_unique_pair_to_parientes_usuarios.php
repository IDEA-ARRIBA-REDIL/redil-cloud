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
        Schema::table('parientes_usuarios', function (Blueprint $table): void {
            $table->unique(['user_id', 'pariente_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parientes_usuarios', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'pariente_user_id']);
        });
    }
};
