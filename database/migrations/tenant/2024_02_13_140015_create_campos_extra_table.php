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
    Schema::create('campos_extra', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 100);
      $table->string('placeholder', 100)->nullable();
      $table->integer('tipo_de_campo');
      $table
        ->boolean('required')
        ->nullable()
        ->default(1);
      $table->string('class_col', 100)->nullable();
      $table->string('class_id', 100)->nullable();
      $table->text('opciones_select')->nullable();
      $table->boolean('visible')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('campos_extra');
  }
};
