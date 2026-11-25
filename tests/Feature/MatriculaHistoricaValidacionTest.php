<?php

namespace Tests\Feature;

use App\Livewire\Matricula\MatriculaModal;
use App\Models\HorarioMateriaPeriodo;
use App\Models\MateriaAprobadaUsuario;
use App\Models\MateriaPeriodo;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MatriculaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MatriculaHistoricaValidacionTest extends TestCase
{
    public function test_matricula_local_respeta_homologaciones_y_requisitos(): void
    {
        if (getenv('REDIL_ENSAYO_MATRICULA_LOCAL') !== '1') {
            $this->markTestSkipped('Ensayo explícito contra el tenant local, siempre con rollback.');
        }
        $this->assertSame('127.0.0.1', config('database.connections.pgsql.host'));
        $this->assertSame('m', config('database.connections.pgsql.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        Bus::fake();
        Tenant::findOrFail('crecer')->run(function (): void {
            $this->assertSame('m', DB::selectOne('select current_database() as db')->db);
            DB::beginTransaction();
            try {
                Model::withoutEvents(function (): void {
                    $user = User::findOrFail(999);
                    $this->assertTrue(MateriaAprobadaUsuario::where('user_id', 999)->where('materia_id', 35)->where('aprobado', 1)->exists());
                    $this->assertFalse(MateriaAprobadaUsuario::where('user_id', 999)->where('materia_id', 36)->where('aprobado', 1)->exists());
                    $periodo = Periodo::findOrFail(35)->replicate();
                    $periodo->nombre = 'ENSAYO TRANSACCIONAL SIGUIENTE PERIODO';
                    $periodo->estado = true;
                    $periodo->fecha_inicio = '2027-01-01';
                    $periodo->fecha_fin = '2027-06-30';
                    $periodo->save();
                    $service = app(MatriculaService::class);
                    $initial = Matricula::count();
                    foreach ([35 => false, 40 => false, 36 => true] as $materiaId => $permitida) {
                        $mp = MateriaPeriodo::where('materia_id', $materiaId)->firstOrFail()->replicate();
                        $mp->periodo_id = $periodo->id;
                        $mp->finalizado = false;
                        $mp->save();
                        $horario = HorarioMateriaPeriodo::firstOrFail()->replicate();
                        $horario->materia_periodo_id = $mp->id;
                        $horario->save();
                        $component = new MatriculaModal;
                        $component->usuario = $user;
                        $component->materiaId = $materiaId;
                        $component->escuelaId = 3;
                        $component->periodoId = $periodo->id;
                        $component->sedeId = $user->sede_id;
                        $component->horarioId = $horario->id;
                        $component->matricular($service);
                        $this->assertSame($initial + ($permitida ? 1 : 0), Matricula::count());
                        $this->assertSame($permitida, Matricula::where('user_id', 999)->where('horario_materia_periodo_id', $horario->id)->exists());
                    }
                });
            } finally {
                DB::rollBack();
            }
        });
    }
}
