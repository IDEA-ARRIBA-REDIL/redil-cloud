<?php

namespace Tests\Unit;

use App\Livewire\Maestros\CalificacionGrillaAlumnos;
use App\Models\MatriculaHorarioMateriaPeriodo;
use App\Models\User;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CalificacionGrillaAlumnosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make('view')->getFinder()->setPaths([resource_path('views')]);
    }

    public function test_filtra_alumnos_por_nombre_documento_y_correo_sin_distinguir_acentos_o_mayusculas(): void
    {
        $componente = new CalificacionGrillaAlumnos;
        $componente->alumnosConEstado = collect([
            $this->estadoAcademico(1, 'Ángela', 'María', 'García', 'Ruiz', '00123', 'angela@example.com'),
            $this->estadoAcademico(2, 'Carlos', null, 'Pérez', null, '98765', 'CARLOS@example.com'),
        ]);

        $this->assertSame([1, 2], $this->idsRenderizados($componente));

        $componente->busquedaAlumno = 'garcia angela';
        $this->assertSame([1], $this->idsRenderizados($componente));

        $componente->busquedaAlumno = 'ruiz';
        $this->assertSame([1], $this->idsRenderizados($componente));

        $componente->busquedaAlumno = '98765';
        $this->assertSame([2], $this->idsRenderizados($componente));

        $componente->busquedaAlumno = 'carlos@EXAMPLE.com';
        $this->assertSame([2], $this->idsRenderizados($componente));

        $componente->busquedaAlumno = 'sin coincidencias';
        $this->assertSame([], $this->idsRenderizados($componente));
    }

    private function estadoAcademico(int $id, string $primerNombre, ?string $segundoNombre, string $primerApellido, ?string $segundoApellido, string $identificacion, string $email): MatriculaHorarioMateriaPeriodo
    {
        $usuario = (new User)->forceFill([
            'id' => $id,
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'identificacion' => $identificacion,
            'email' => $email,
        ]);
        $estado = (new MatriculaHorarioMateriaPeriodo)->forceFill(['user_id' => $id]);
        $estado->setRelation('user', $usuario);

        return $estado;
    }

    /**
     * @return array<int, int>
     */
    private function idsRenderizados(CalificacionGrillaAlumnos $componente): array
    {
        /** @var Collection<int, MatriculaHorarioMateriaPeriodo> $alumnos */
        $alumnos = $componente->render()->getData()['alumnosFiltrados'];

        return $alumnos->pluck('user_id')->all();
    }
}
