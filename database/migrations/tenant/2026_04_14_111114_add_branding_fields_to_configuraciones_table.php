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
            $table->boolean('marca_blanca')->default(false)->after('nombre_app_personalizado');
            $table->string('nombre_creador')->nullable()->after('marca_blanca');
            $table->string('url_creador')->nullable()->after('nombre_creador');
            $table->string('color_nombre_app')->nullable()->after('url_creador');
            $table->string('descripcion_login')->nullable()->after('color_nombre_app');
            $table->string('sufijo_app')->nullable()->after('descripcion_login');
            $table->string('version_app')->nullable()->after('sufijo_app');
            $table->string('logo_app')->nullable()->after('version_app');
            $table->string('favicon_app')->nullable()->after('logo_app');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->dropColumn([
                'marca_blanca',
                'nombre_creador',
                'url_creador',
                'color_nombre_app',
                'descripcion_login',
                'sufijo_app',
                'version_app',
                'logo_app',
                'favicon_app'
            ]);
        });
    }
};
