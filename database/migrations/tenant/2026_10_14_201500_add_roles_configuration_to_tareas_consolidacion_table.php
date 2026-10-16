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
        Schema::table('tareas_consolidacion', function (Blueprint $table) {
            $table->boolean('todos_los_roles')->default(true)->after('default');
        });

        Schema::create('rol_tarea_consolidacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_consolidacion_id')->constrained('tareas_consolidacion')->onDelete('cascade');
            $table->foreignId('rol_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rol_tarea_consolidacion');

        Schema::table('tareas_consolidacion', function (Blueprint $table) {
            $table->dropColumn('todos_los_roles');
        });
    }
};
