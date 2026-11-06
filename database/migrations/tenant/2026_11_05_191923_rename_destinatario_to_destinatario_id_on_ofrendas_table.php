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
        Schema::table('ofrendas', function (Blueprint $table): void {
            $table->renameColumn('destinatario', 'destinatario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ofrendas', function (Blueprint $table): void {
            $table->renameColumn('destinatario_id', 'destinatario');
        });
    }
};
