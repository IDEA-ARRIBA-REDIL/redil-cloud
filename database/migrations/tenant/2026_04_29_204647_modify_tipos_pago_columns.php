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
        Schema::table('tipos_pago', function (Blueprint $table) {
            $table->string('nombre', 100)->change();
            $table->string('enlace', 100)->nullable()->change();
            $table->string('imagen', 100)->nullable()->change();
            $table->string('cuenta_sap', 100)->nullable()->change();
            $table->text('observaciones')->nullable()->change();
            $table->string('color', 100)->nullable()->change();
            $table->string('fondo', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_pago', function (Blueprint $table) {
            $table->string('nombre', 30)->change();
            $table->string('enlace', 100)->nullable(false)->change();
            $table->string('imagen', 100)->nullable(false)->change();
            $table->string('cuenta_sap', 30)->nullable(false)->change();
            $table->text('observaciones')->nullable(false)->change();
            $table->string('color', 30)->nullable()->change();
            $table->string('fondo', 30)->nullable()->change();
        });
    }
};
