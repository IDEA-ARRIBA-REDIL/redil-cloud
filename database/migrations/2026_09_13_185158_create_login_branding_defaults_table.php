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
        Schema::create('login_branding_defaults', function (Blueprint $table) {
            $table->id();
            $table->boolean('singleton_key')->default(true)->unique();
            $table->string('left_image_path')->nullable();
            $table->unsignedInteger('carousel_interval_ms')->default(6000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_branding_defaults');
    }
};
