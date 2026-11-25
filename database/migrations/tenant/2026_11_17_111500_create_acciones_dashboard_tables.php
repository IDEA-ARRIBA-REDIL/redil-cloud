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
        if (! Schema::hasTable('acciones_dashboard')) {
            Schema::create('acciones_dashboard', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 80);
                $table->string('url', 500);
                $table->boolean('abrir_nueva_pestana')->default(false);
                $table->string('tipo_visual', 20)->default('imagen');
                $table->string('icono_clase', 100)->nullable();
                $table->string('icono_color', 30)->nullable()->default('#2F5D50');
                $table->string('imagen', 255)->nullable();
                $table->boolean('visible_todos')->default(true);
                $table->integer('orden')->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('accion_dashboard_tipo_usuario')) {
            Schema::create('accion_dashboard_tipo_usuario', function (Blueprint $table) {
                $table->id();
                $table->foreignId('accion_dashboard_id')
                    ->constrained('acciones_dashboard')
                    ->cascadeOnDelete();
                $table->foreignId('tipo_usuario_id')
                    ->constrained('tipo_usuarios')
                    ->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['accion_dashboard_id', 'tipo_usuario_id'], 'accion_dash_tipo_usr_unique');
            });
        }

        if (! Schema::hasTable('accion_dashboard_role')) {
            Schema::create('accion_dashboard_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('accion_dashboard_id')
                    ->constrained('acciones_dashboard')
                    ->cascadeOnDelete();
                $table->foreignId('role_id')
                    ->constrained('roles')
                    ->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['accion_dashboard_id', 'role_id'], 'accion_dash_role_unique');
            });
        }

        if (Schema::hasTable('acciones_dashboard') && DB::table('acciones_dashboard')->count() === 0) {
            $ahora = now();
            DB::table('acciones_dashboard')->insert([
                [
                    'nombre' => 'Academia',
                    'url' => '/escuelas/dashboard',
                    'abrir_nueva_pestana' => false,
                    'tipo_visual' => 'imagen',
                    'icono_clase' => 'ti ti-school',
                    'icono_color' => '#1d4ed8',
                    'imagen' => 'Funcionalidad-escuelas.png',
                    'visible_todos' => true,
                    'orden' => 1,
                    'activo' => true,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ],
                [
                    'nombre' => 'Grupos',
                    'url' => '/grupos',
                    'abrir_nueva_pestana' => false,
                    'tipo_visual' => 'imagen',
                    'icono_clase' => 'ti ti-users-group',
                    'icono_color' => '#4d9a1d',
                    'imagen' => 'Funcionalidad-grupos.png',
                    'visible_todos' => true,
                    'orden' => 2,
                    'activo' => true,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accion_dashboard_role');
        Schema::dropIfExists('accion_dashboard_tipo_usuario');
        Schema::dropIfExists('acciones_dashboard');
    }
};
