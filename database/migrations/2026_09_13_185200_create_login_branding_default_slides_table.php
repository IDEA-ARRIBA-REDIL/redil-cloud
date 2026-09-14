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
        Schema::create('login_branding_default_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('login_branding_default_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('alt_text', 150)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['login_branding_default_id', 'is_active', 'sort_order'], 'login_default_slides_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_branding_default_slides');
    }
};
