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
        Schema::create('biblia_versiones', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('nombre', 150);
            $table->string('abreviatura', 30);
            $table->unsignedSmallInteger('orden')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('biblia_libros', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('nombre', 100);
            $table->string('abreviatura', 20);
            $table->string('usfm', 20)->nullable()->index();
            $table->string('testamento', 50);
            $table->unsignedSmallInteger('capitulos')->default(1);
            $table->unsignedSmallInteger('orden')->default(1)->index();
            $table->timestamps();
        });

        Schema::create('biblia_versiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('biblia_versiones')->cascadeOnDelete();
            $table->foreignId('libro_id')->constrained('biblia_libros')->cascadeOnDelete();
            $table->unsignedSmallInteger('capitulo');
            $table->unsignedSmallInteger('versiculo');
            $table->text('texto');
            $table->text('encabezado')->nullable();
            $table->timestamps();

            $table->index(['version_id', 'libro_id', 'capitulo'], 'idx_biblia_versiculo_lectura');
            $table->unique(['version_id', 'libro_id', 'capitulo', 'versiculo'], 'uniq_biblia_versiculo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biblia_versiculos');
        Schema::dropIfExists('biblia_libros');
        Schema::dropIfExists('biblia_versiones');
    }
};
