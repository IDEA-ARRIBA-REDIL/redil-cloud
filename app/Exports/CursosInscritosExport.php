<?php

namespace App\Exports;

use App\Models\CursoUser;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CursosInscritosExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $cursoId;

    protected $carreraId;

    protected $fechaInicio;

    protected $fechaFin;

    protected $tieneAccesoTotal;

    protected $carrerasPermitidasIds;

    /**
     * @param  int|null  $cursoId
     * @param  int|null  $carreraId
     * @param  string|null  $fechaInicio
     * @param  string|null  $fechaFin
     * @param  bool  $tieneAccesoTotal
     * @param  array  $carrerasPermitidasIds
     */
    public function __construct($cursoId = null, $carreraId = null, $fechaInicio = null, $fechaFin = null, $tieneAccesoTotal = true, $carrerasPermitidasIds = [])
    {
        $this->cursoId = $cursoId;
        $this->carreraId = $carreraId;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->tieneAccesoTotal = $tieneAccesoTotal;
        $this->carrerasPermitidasIds = $carrerasPermitidasIds;
    }

    public function query()
    {
        $query = CursoUser::query()
            ->with(['user.sede', 'user.entidadRelacionada', 'user.pais', 'user.estadoCivil', 'curso.carrera'])
            ->leftJoin('users', 'curso_users.user_id', '=', 'users.id')
            ->leftJoin('cursos', 'curso_users.curso_id', '=', 'cursos.id');

        if (!$this->tieneAccesoTotal) {
            if (empty($this->carrerasPermitidasIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('cursos.carrera_id', $this->carrerasPermitidasIds);
            }
        }

        if ($this->cursoId) {
            $query->where('curso_users.curso_id', $this->cursoId);
        }

        if ($this->carreraId) {
            if (is_array($this->carreraId)) {
                $query->whereIn('cursos.carrera_id', array_filter($this->carreraId));
            } else {
                $query->where('cursos.carrera_id', $this->carreraId);
            }
        }

        if ($this->fechaInicio) {
            $query->whereDate('curso_users.fecha_inscripcion', '>=', $this->fechaInicio);
        }

        if ($this->fechaFin) {
            $query->whereDate('curso_users.fecha_inscripcion', '<=', $this->fechaFin);
        }

        return $query->select('curso_users.*');
    }

    public function headings(): array
    {
        return [
            'Nombre Completo',
            'Identificación',
            'Correo Electrónico',
            'Teléfono',
            'Género',
            'Edad',
            'País',
            'Estado Civil',
            'Organización',
            'Sede',
            'Curso',
            'Carrera',
            'Progreso (%)',
            'Fecha Inscripción',
            'Última Actualización Progreso',
        ];
    }

    /**
     * @param  CursoUser  $inscripcion
     */
    public function map($inscripcion): array
    {
        $user = $inscripcion->user;

        return [
            $user ? $user->nombre(4) : 'N/A',
            $user ? $user->identificacion : 'N/A',
            $user ? $user->email : 'N/A',
            $user ? $user->telefono_movil : 'N/A',
            $user ? ($user->genero == 1 ? 'Femenino' : 'Masculino') : 'N/A',
            $user && $user->fecha_nacimiento ? $user->edad() : 'N/A',
            ($user && $user->pais) ? $user->pais->nombre : 'N/A',
            ($user && $user->estadoCivil) ? $user->estadoCivil->nombre : 'N/A',
            ($user && $user->entidadRelacionada) ? $user->entidadRelacionada->nombre : 'N/A',
            ($user && $user->sede) ? $user->sede->nombre : 'N/A',
            $inscripcion->curso ? $inscripcion->curso->nombre : 'N/A',
            ($inscripcion->curso && $inscripcion->curso->carrera) ? $inscripcion->curso->carrera->nombre : 'N/A',
            $inscripcion->porcentaje_progreso.'%',
            Carbon::parse($inscripcion->fecha_inscripcion)->format('d/m/Y H:i'),
            $inscripcion->updated_at ? Carbon::parse($inscripcion->updated_at)->format('d/m/Y H:i') : 'N/A',
        ];
    }
}
