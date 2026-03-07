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
    Schema::create('gestion_videos', function (Blueprint $table) {
      $table->id();
      $table->string('nombre');
      $table->text('url_video'); // Puede ser un iframe de YouTube o un link mp4
      $table->date('fecha_publicacion')->nullable();
      $table->boolean('visible')->default(true);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('gestion_videos');
  }
};
