<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('tipos_baja_alta', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('nombre', 200);
      $table->boolean('dado_baja')->default(0);
      $table->boolean('dado_alta')->default(0);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tipos_baja_alta');
  }
};
