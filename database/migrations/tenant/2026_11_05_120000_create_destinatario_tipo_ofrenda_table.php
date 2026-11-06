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
        if (! Schema::hasTable('destinatario_tipo_ofrenda')) {
            Schema::create('destinatario_tipo_ofrenda', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('destinatario_id')->constrained('destinatarios')->onDelete('cascade');
                $table->foreignId('tipo_ofrenda_id')->constrained('tipo_ofrenda')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinatario_tipo_ofrenda');
    }
};
