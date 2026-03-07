<?php

namespace App\Exports;

use App\Models\Matricula;
use App\Models\Periodo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AlumnosPeriodoExport implements FromQuery, WithHeadings, WithMapping
{
    protected $periodo;
    protected $filtroMateriaPeriodo;
    protected $filtroSedeAlumno;
    protected $filtroSedeMatricula;

    public function __construct(Periodo $periodo, $filtroMateriaPeriodo, array $filtroSedeAlumno, array $filtroSedeMatricula)
    {
        $this->periodo = $periodo;
        $this->filtroMateriaPeriodo = $filtroMateriaPeriodo;
        $this->filtroSedeAlumno = $filtroSedeAlumno;
        $this->filtroSedeMatricula = $filtroSedeMatricula;
    }

    /**
     * Define los encabezados de las columnas en el Excel.
     */
    public function headings(): array
    {
        return [
            'ID Alumno',
            'Nombre Completo',
            'Identificacion',
            'Email',
            'Teléfono Móvil',
            'Bloqueado',
            'Trasladado',
            'Materia',
            'Horario',
            'Aula',
            'Sede Matricula',
            'Sede Alumno',
            'Sede Material',      // NUEVO
            'Fecha Matrícula',    // NUEVO
            'Método Pago',        // NUEVO
        ];
    }

    /**
     * Transforma cada resultado de la consulta en una fila del Excel.
     * @param Matricula $matricula
     */
    public function map($matricula): array
    {
        // Lógica para obtener el método de pago desde la inscripción asociada a la actividad del periodo
        $metodoPago = 'N/A';

        // Buscamos la inscripción que coincida con la actividad de este periodo (gracias al eager loading filtrado)
        $inscripcion = $matricula->user->inscripciones->first();

        if ($inscripcion && $inscripcion->compra && $inscripcion->compra->metodoPago) {
            $metodoPago = $inscripcion->compra->metodoPago->nombre;
        }

        return [
            $matricula->user->id,
            $matricula->user->nombre(3),
            $matricula->user->identificacion ?? 'N/A',
            $matricula->user->email,
            $matricula->user->telefono_movil ?? 'N/A',
            $matricula->bloqueado ? 'Si' : 'N/A',
            $matricula->traslados_log_count > 0 ? 'Si' : 'N/A',
            $matricula->horarioMateriaPeriodo->materiaPeriodo->materia->nombre,
            $matricula->horarioMateriaPeriodo->horarioBase->dia_semana . ' | ' . $matricula->horarioMateriaPeriodo->horarioBase->hora_inicio_formato . ' - ' . $matricula->horarioMateriaPeriodo->horarioBase->hora_fin_formato,
            $matricula->horarioMateriaPeriodo->horarioBase->aula->nombre,
            $matricula->horarioMateriaPeriodo->horarioBase->aula->sede->nombre,
            $matricula->user->sede->nombre ?? 'N/A',
            $matricula->materialSede->nombre ?? 'N/A',            // NUEVO
            $matricula->fecha_matricula ? $matricula->fecha_matricula->format('d/m/Y') : 'N/A', // NUEVO
            $metodoPago,                                          // NUEVO
        ];
    }

    /**
     * Define la consulta a la base de datos que obtiene los datos.
     */
    public function query()
    {
        // 1. Identificar la Actividad vinculada al Periodo actual
        // Asumimos que la Escuela crea una Actividad y la vincula al Periodo.
        $actividadId = \App\Models\Actividad::where('periodo_id', $this->periodo->id)->value('id');

        // La consulta base se hace sobre las Matrículas para obtener una fila por inscripción.
        $query = Matricula::query()
            ->with([
                'user.sede',
                'materialSede', // <-- Eager loading para la nueva columna Sede Material
                'horarioMateriaPeriodo.materiaPeriodo.materia',
                'horarioMateriaPeriodo.horarioBase.aula.sede',
                // Eager loading condicional complejo para obtener inscripciones de la actividad correcta
                'user.inscripciones' => function ($q) use ($actividadId) {
                    if ($actividadId) {
                        $q->whereHas('categoriaActividad', function ($qCat) use ($actividadId) {
                            $qCat->where('actividad_id', $actividadId);
                        });
                    }
                    $q->with('compra.metodoPago');
                }
            ])
            ->withCount('trasladosLog')
            ->where('periodo_id', $this->periodo->id)
            ->join('users', 'matriculas.user_id', '=', 'users.id')
            ->select('matriculas.*')
            ->orderBy('users.primer_nombre');


        // REPLICAMOS LA LÓGICA DE FILTRADO DEL COMPONENTE LIVEWIRE

        // Si se seleccionó una materia, se aplica como filtro principal
        if ($this->filtroMateriaPeriodo) {
            $query->whereHas('horarioMateriaPeriodo', function ($horarioQuery) {
                $horarioQuery->where('materia_periodo_id', $this->filtroMateriaPeriodo);
            });
        }

        // Si se seleccionó alguna sede (de alumno o de matrícula)
        if (!empty($this->filtroSedeAlumno) || !empty($this->filtroSedeMatricula)) {
            $query->where(function ($userOrMatriculaQuery) {
                if (!empty($this->filtroSedeAlumno)) {
                    $userOrMatriculaQuery->whereIn('matriculas.user_id', function ($userQuery) {
                        $userQuery->select('id')->from('users')->whereIn('sede_id', $this->filtroSedeAlumno);
                    });
                }
                if (!empty($this->filtroSedeMatricula)) {
                    $userOrMatriculaQuery->orWhereHas('horarioMateriaPeriodo.horarioBase.aula.sede', function ($sedeQuery) {
                        $sedeQuery->whereIn('sedes.id', $this->filtroSedeMatricula);
                    });
                }
            });
        }

        return $query;
    }
}
