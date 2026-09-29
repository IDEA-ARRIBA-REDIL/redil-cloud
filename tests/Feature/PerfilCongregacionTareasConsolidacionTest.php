<?php

namespace Tests\Feature;

use App\Models\EstadoTareaConsolidacion;
use App\Models\TareaConsolidacion;
use App\Models\TareaConsolidacionUsuario;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PerfilCongregacionTareasConsolidacionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'consolidacion_testing');
        config()->set('database.connections.consolidacion_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('view.compiled', base_path('storage/framework/views'));
        $this->app->forgetInstance('blade.compiler');
        Blade::clearResolvedInstance('blade.compiler');
        $this->app->make('view.engine.resolver')->forget('blade');
        config()->set('logging.default', 'null');
        DB::purge('consolidacion_testing');
        DB::setDefaultConnection('consolidacion_testing');

        Schema::create('tareas_consolidacion', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->integer('orden');
            $table->boolean('default')->default(false);
            $table->timestamps();
        });
        Schema::create('estados_tarea_consolidacion', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('color');
            $table->timestamps();
        });
        Schema::create('tarea_consolidacion_usuario', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tarea_consolidacion_id');
            $table->unsignedBigInteger('estado_tarea_consolidacion_id')->nullable();
            $table->date('fecha')->nullable();
            $table->timestamps();
        });
    }

    public function test_shows_only_the_profile_person_tasks_in_configured_order(): void
    {
        $this->assignTask(10, 'Segunda tarea', 2);
        $this->assignTask(10, 'Primera tarea', 1);
        $this->assignTask(20, 'Tarea de otra persona', 0);

        $html = $this->renderProfileTasks(10);

        $this->assertStringContainsString('Finalizado', $html);
        $this->assertStringContainsString('27 septiembre 2026', $html);
        $this->assertStringNotContainsString('Tarea de otra persona', $html);
        $this->assertLessThan(strpos($html, 'Segunda tarea'), strpos($html, 'Primera tarea'));
    }

    public function test_empty_profile_does_not_assign_default_tasks(): void
    {
        TareaConsolidacion::query()->create(['nombre' => 'Tarea por defecto', 'orden' => 1, 'default' => true]);

        $html = $this->renderProfileTasks(10);

        $this->assertStringContainsString('Esta persona no tiene tareas de consolidación asignadas.', $html);
        $this->assertStringNotContainsString('Tarea por defecto', $html);
        $this->assertSame(0, TareaConsolidacionUsuario::query()->count());
    }

    public function test_handles_missing_task_state_and_date(): void
    {
        TareaConsolidacionUsuario::query()->insert([
            'user_id' => 10, 'tarea_consolidacion_id' => 999,
            'estado_tarea_consolidacion_id' => 999, 'fecha' => null,
        ]);

        $html = $this->renderProfileTasks(10);

        foreach (['Tarea no disponible', 'Sin estado', 'Sin fecha', 'bg-label-secondary'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }

    public function test_read_only_render_has_no_controls_or_writes_and_no_per_row_queries(): void
    {
        for ($index = 1; $index <= 8; $index++) {
            $this->assignTask(10, 'Tarea '.$index, $index);
        }
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();

        $html = $this->renderProfileTasks(10);
        $queries = DB::connection()->getQueryLog();

        $this->assertCount(3, $queries);
        foreach ($queries as $query) {
            $this->assertStringStartsWith('select ', strtolower(ltrim($query['query'])));
        }
        $this->assertDoesNotMatchRegularExpression('/<(?:a|button|form|input|select|textarea)\b|wire:/i', $html);
    }

    public function test_escapes_task_names_and_rejects_unknown_state_colors(): void
    {
        $this->assignTask(10, '<script>alert(1)</script>', 1, 'unknown-color');

        $html = $this->renderProfileTasks(10);

        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('bg-label-secondary', $html);
        $this->assertStringNotContainsString('unknown-color', $html);
    }

    private function renderProfileTasks(int $userId): string
    {
        $usuario = new User;
        $usuario->forceFill(['id' => $userId]);

        return Blade::render('<x-tareas-consolidacion-perfil :usuario="$usuario" />', ['usuario' => $usuario]);
    }

    private function assignTask(int $userId, string $name, int $order, string $color = 'success'): void
    {
        $task = TareaConsolidacion::query()->create(['nombre' => $name, 'orden' => $order]);
        $state = EstadoTareaConsolidacion::query()->create(['nombre' => 'Finalizado', 'color' => $color]);

        TareaConsolidacionUsuario::query()->insert([
            'user_id' => $userId,
            'tarea_consolidacion_id' => $task->id,
            'estado_tarea_consolidacion_id' => $state->id,
            'fecha' => '2026-09-27',
        ]);
    }
}
