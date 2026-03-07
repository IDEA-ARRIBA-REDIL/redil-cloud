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
    Schema::create('campos_informe_excel', function (Blueprint $table) {
      $table->id();
      $table->string('nombre_campo_bd', 200)->nullable();
      $table->string('nombre_campo_informe', 200)->nullable();
      $table->integer('selector_id');
      $table->string('tabla', 200)->nullable();
      $table->boolean('raw_sql')->nullable();
      $table->boolean('eloquent_sql')->nullable();
      $table->integer('orden');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('campos_informe_excel');
  }
};
