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
        $missingColumns = [
            'enlace_digital' => ! Schema::hasColumn('productos_tienda', 'enlace_digital'),
            'visible_todos' => ! Schema::hasColumn('productos_tienda', 'visible_todos'),
            'genero' => ! Schema::hasColumn('productos_tienda', 'genero'),
        ];

        if (! in_array(true, $missingColumns, true)) {
            return;
        }

        Schema::table('productos_tienda', function (Blueprint $table) use ($missingColumns): void {
            if ($missingColumns['enlace_digital']) {
                $table->text('enlace_digital')->nullable();
            }

            if ($missingColumns['visible_todos']) {
                $table->boolean('visible_todos')->default(true);
            }

            if ($missingColumns['genero']) {
                $table->integer('genero')->default(3);
            }
        });
    }

    /**
     * Retain these columns on rollback because the create-table migration also defines them.
     */
    public function down(): void {}
};
