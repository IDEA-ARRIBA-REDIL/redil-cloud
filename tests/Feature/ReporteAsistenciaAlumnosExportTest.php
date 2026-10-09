<?php

namespace Tests\Feature;

use App\Exports\AsistenciasClaseExport;
use App\Livewire\Maestros\ReporteAsistenciaAlumnos;
use App\Models\HorarioMateriaPeriodo;
use App\Models\Maestro;
use App\Models\ReporteAsistenciaClase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Mockery;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ReporteAsistenciaAlumnosExportTest extends TestCase
{
    public function test_exporta_todos_los_reportes_de_la_clase(): void
    {
        $componente = $this->componente();
        $respuesta = new BinaryFileResponse(__FILE__);
        Excel::shouldReceive('download')->once()
            ->with(Mockery::type(AsistenciasClaseExport::class), 'asistencias-clase-12.xlsx')
            ->andReturn($respuesta);

        $this->assertSame($respuesta, $componente->exportarTodosLosReportes());
    }

    public function test_exporta_un_reporte_de_la_clase_seleccionada(): void
    {
        $componente = $this->componente();
        $reporte = (new ReporteAsistenciaClase)->forceFill(['id' => 34]);
        $relacion = Mockery::mock(HasMany::class);
        $componente->horarioAsignado->shouldReceive('reportesAsistencia')->once()->andReturn($relacion);
        $relacion->shouldReceive('findOrFail')->once()->with(34)->andReturn($reporte);
        $respuesta = new BinaryFileResponse(__FILE__);
        Excel::shouldReceive('download')->once()
            ->with(Mockery::type(AsistenciasClaseExport::class), 'asistencias-reporte-34.xlsx')
            ->andReturn($respuesta);

        $this->assertSame($respuesta, $componente->exportarReporte(34));
    }

    public function test_rechaza_reportes_ajenos_o_inexistentes(): void
    {
        $componente = $this->componente();
        $relacion = Mockery::mock(HasMany::class);
        $componente->horarioAsignado->shouldReceive('reportesAsistencia')->once()->andReturn($relacion);
        $relacion->shouldReceive('findOrFail')->with(99)->andThrow(ModelNotFoundException::class);
        Excel::shouldReceive('download')->never();

        $this->expectException(ModelNotFoundException::class);
        $componente->exportarReporte(99);
    }

    public function test_rechaza_exportacion_sin_autenticacion(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);
        Excel::shouldReceive('download')->never();
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(0);

        (new ReporteAsistenciaAlumnos)->exportarTodosLosReportes();
    }

    public function test_rechaza_una_clase_no_asignada_al_maestro(): void
    {
        $componente = $this->componente(false);
        Excel::shouldReceive('download')->never();
        $this->expectException(HttpException::class);

        $componente->exportarTodosLosReportes();
    }

    private function componente(bool $asignado = true): ReporteAsistenciaAlumnos
    {
        Auth::shouldReceive('check')->once()->andReturn(true);
        $horario = Mockery::mock(HorarioMateriaPeriodo::class)->makePartial();
        $horario->setAttribute('id', 12);
        $relacion = Mockery::mock(BelongsToMany::class);
        $horario->shouldReceive('maestros')->once()->andReturn($relacion);
        $relacion->shouldReceive('whereKey')->once()->with(7)->andReturnSelf();
        $relacion->shouldReceive('exists')->once()->andReturn($asignado);
        $componente = new ReporteAsistenciaAlumnos;
        $componente->horarioAsignado = $horario;
        $componente->maestro = (new Maestro)->forceFill(['id' => 7]);

        return $componente;
    }
}
