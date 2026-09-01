<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // 1. Modificar tabla materias_aprobada_usuario
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE materias_aprobada_usuario ALTER COLUMN aprobado DROP DEFAULT');
            DB::statement('ALTER TABLE materias_aprobada_usuario ALTER COLUMN aprobado TYPE smallint USING (CASE WHEN aprobado IS TRUE THEN 1 WHEN aprobado IS FALSE THEN 0 ELSE 2 END)');
            DB::statement('ALTER TABLE materias_aprobada_usuario ALTER COLUMN aprobado SET DEFAULT 2');
        } else {
            Schema::table('materias_aprobada_usuario', function (Blueprint $table) {
                $table->unsignedTinyInteger('aprobado')
                    ->default(2)
                    ->comment('0: Reprobado, 1: Aprobado, 2: En proceso')
                    ->change();
            });
        }

        Schema::table('materias_aprobada_usuario', function (Blueprint $table) {
            if (! Schema::hasColumn('materias_aprobada_usuario', 'fecha_homologacion_aprobacion')) {
                $table->timestamp('fecha_homologacion_aprobacion')
                    ->nullable()
                    ->after('fecha_homologacion')
                    ->comment('Fecha y hora exacta de aprobación u homologación');
            }
        });

        // Convertir registros previos de materias_aprobada_usuario
        DB::table('materias_aprobada_usuario')
            ->where('aprobado', 1)
            ->whereNull('fecha_homologacion_aprobacion')
            ->update([
                'fecha_homologacion_aprobacion' => DB::raw('COALESCE(fecha_homologacion, updated_at, created_at)'),
            ]);

        // 2. Modificar tabla niveles_aprobado_usuario
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE niveles_aprobado_usuario ALTER COLUMN aprobado DROP DEFAULT');
            DB::statement('ALTER TABLE niveles_aprobado_usuario ALTER COLUMN aprobado TYPE smallint USING (CASE WHEN aprobado IS TRUE THEN 1 WHEN aprobado IS FALSE THEN 0 ELSE 2 END)');
            DB::statement('ALTER TABLE niveles_aprobado_usuario ALTER COLUMN aprobado SET DEFAULT 2');
        } else {
            Schema::table('niveles_aprobado_usuario', function (Blueprint $table) {
                $table->unsignedTinyInteger('aprobado')
                    ->default(2)
                    ->comment('0: Reprobado, 1: Aprobado, 2: En proceso')
                    ->change();
            });
        }

        Schema::table('niveles_aprobado_usuario', function (Blueprint $table) {
            if (! Schema::hasColumn('niveles_aprobado_usuario', 'fecha_homologacion_aprobacion')) {
                $table->timestamp('fecha_homologacion_aprobacion')
                    ->nullable()
                    ->after('fecha_homologacion')
                    ->comment('Fecha y hora exacta de aprobación u homologación');
            }
        });

        // Convertir registros previos de niveles_aprobado_usuario
        DB::table('niveles_aprobado_usuario')
            ->where('aprobado', 1)
            ->whereNull('fecha_homologacion_aprobacion')
            ->update([
                'fecha_homologacion_aprobacion' => DB::raw('COALESCE(fecha_homologacion, updated_at, created_at)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (Schema::hasColumn('materias_aprobada_usuario', 'fecha_homologacion_aprobacion')) {
            Schema::table('materias_aprobada_usuario', function (Blueprint $table) {
                $table->dropColumn('fecha_homologacion_aprobacion');
            });
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE materias_aprobada_usuario ALTER COLUMN aprobado DROP DEFAULT');
            DB::statement('ALTER TABLE materias_aprobada_usuario ALTER COLUMN aprobado TYPE boolean USING (CASE WHEN aprobado = 1 THEN true ELSE false END)');
            DB::statement('ALTER TABLE materias_aprobada_usuario ALTER COLUMN aprobado SET DEFAULT false');
        } else {
            Schema::table('materias_aprobada_usuario', function (Blueprint $table) {
                $table->boolean('aprobado')->default(false)->change();
            });
        }

        if (Schema::hasColumn('niveles_aprobado_usuario', 'fecha_homologacion_aprobacion')) {
            Schema::table('niveles_aprobado_usuario', function (Blueprint $table) {
                $table->dropColumn('fecha_homologacion_aprobacion');
            });
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE niveles_aprobado_usuario ALTER COLUMN aprobado DROP DEFAULT');
            DB::statement('ALTER TABLE niveles_aprobado_usuario ALTER COLUMN aprobado TYPE boolean USING (CASE WHEN aprobado = 1 THEN true ELSE false END)');
            DB::statement('ALTER TABLE niveles_aprobado_usuario ALTER COLUMN aprobado SET DEFAULT false');
        } else {
            Schema::table('niveles_aprobado_usuario', function (Blueprint $table) {
                $table->boolean('aprobado')->default(false)->change();
            });
        }
    }
};
