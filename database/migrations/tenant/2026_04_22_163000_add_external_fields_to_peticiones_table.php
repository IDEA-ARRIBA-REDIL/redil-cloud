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
        Schema::table('peticiones', function (Blueprint $table) {
            $table->string('nombre_externo')->nullable()->after('user_id');
            $table->string('email_externo')->nullable()->after('nombre_externo');
            $table->string('telefono_externo')->nullable()->after('email_externo');
            $table->integer('genero_externo')->nullable()->after('telefono_externo')->comment('0=Hombre, 1=Mujer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peticiones', function (Blueprint $table) {
            $table->dropColumn(['nombre_externo', 'email_externo', 'telefono_externo', 'genero_externo']);
        });
    }
};
