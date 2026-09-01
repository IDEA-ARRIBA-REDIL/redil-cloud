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
            'Sede Material',
            'Fecha Matrícula',
            'Método Pago',
            'Punto de Pago',
            'Asesor',
        ];
    }

    /**
     * Transforma cada resultado de la consulta en una fila del Excel.
     *
     * @param  Matricula  $matricula
     */
    public function map($matricula): array
    {
        $inscripcion = $matricula->user?->inscripciones?->first();

        // 1. Obtener Método de Pago (prioridad: matricula -> pago -> compra -> inscripcion)
        $tipoPagoObjeto = $matricula->tipoPago
            ?? $matricula->pago?->tipoPago
            ?? $matricula->compra?->metodoPago
            ?? $matricula->compra?->pagos?->first()?->tipoPago
            ?? $inscripcion?->compra?->metodoPago
            ?? $inscripcion?->compra?->pagos?->first()?->tipoPago;

        $metodoPago = $tipoPagoObjeto?->nombre ?? 'N/A';

        // 2. Obtener Punto de Pago (prioridad: pago de matricula -> pagos de compra -> pagos de inscripción)
        $pagoConCaja = $matricula->pago?->caja
            ? $matricula->pago
            : ($matricula->compra?->pagos?->first(fn ($p) => $p->caja !== null)
                ?? $inscripcion?->compra?->pagos?->first(fn ($p) => $p->caja !== null));

        $puntoDePago = $pagoConCaja?->caja?->puntoDePago?->nombre ?? 'N/A';

        // 3. Obtener Asesor / Cajero (usuario de la caja)
        $asesorUser = $pagoConCaja?->caja?->usuario;
        $asesor = $asesorUser ? $asesorUser->nombre(3) : 'N/A';

        return [
            $matricula->user?->id ?? 'N/A',
            $matricula->user?->nombre(3) ?? 'N/A',
            $matricula->user?->identificacion ?? 'N/A',
            $matricula->user?->email ?? 'N/A',
            $matricula->user?->telefono_movil ?? 'N/A',
            $matricula->bloqueado ? 'Si' : 'N/A',
            $matricula->traslados_log_count > 0 ? 'Si' : 'N/A',
            $matricula->horarioMateriaPeriodo?->materiaPeriodo?->materia?->nombre ?? 'N/A',
            $matricula->horarioMateriaPeriodo?->horarioBase
                ? $matricula->horarioMateriaPeriodo->horarioBase->dia_semana.' | '.$matricula->horarioMateriaPeriodo->horarioBase->hora_inicio_formato.' - '.$matricula->horarioMateriaPeriodo->horarioBase->hora_fin_formato
                : 'N/A',
            $matricula->horarioMateriaPeriodo?->horarioBase?->aula?->nombre ?? 'N/A',
            $matricula->horarioMateriaPeriodo?->horarioBase?->aula?->sede?->nombre ?? 'N/A',
            $matricula->user?->sede?->nombre ?? 'N/A',
            $matricula->materialSede?->nombre ?? 'N/A',
            $matricula->fecha_matricula ? $matricula->fecha_matricula->format('d/m/Y') : 'N/A',
            $metodoPago,
            $puntoDePago,
            $asesor,
        ];
    }

    /**
     * Define la consulta a la base de datos que obtiene los datos.
     */
    public function query()
    {
        // 1. Identificar la Actividad vinculada al Periodo actual
        $actividadId = \App\Models\Actividad::where('periodo_id', $this->periodo->id)->value('id');

        // La consulta base se hace sobre las Matrículas
        $query = Matricula::query()
            ->with([
                'user.sede',
                'materialSede',
                'horarioMateriaPeriodo.materiaPeriodo.materia',
                'horarioMateriaPeriodo.horarioBase.aula.sede',
                'tipoPago',
                'pago.tipoPago',
                'pago.caja.puntoDePago',
                'pago.caja.usuario',
                'compra.metodoPago',
                'compra.pagos.tipoPago',
                'compra.pagos.caja.puntoDePago',
                'compra.pagos.caja.usuario',
                'user.inscripciones' => function ($q) use ($actividadId) {
                    if ($actividadId) {
                        $q->whereHas('categoriaActividad', function ($qCat) use ($actividadId) {
                            $qCat->where('actividad_id', $actividadId);
                        });
                    }
                    $q->with([
                        'compra.metodoPago',
                        'compra.pagos.tipoPago',
                        'compra.pagos.caja.puntoDePago',
                        'compra.pagos.caja.usuario',
                    ]);
                },
            ])
            ->withCount('trasladosLog')
            ->where('periodo_id', $this->periodo->id)
            ->join('users', 'matriculas.user_id', '=', 'users.id')
            ->select('matriculas.*')
            ->orderBy('users.primer_nombre');

        // REPLICAMOS LA LÓGICA DE FILTRADO DEL COMPONENTE LIVEWIRE
        if ($this->filtroMateriaPeriodo) {
            $query->whereHas('horarioMateriaPeriodo', function ($horarioQuery) {
                $horarioQuery->where('materia_periodo_id', $this->filtroMateriaPeriodo);
            });
        }

        if (! empty($this->filtroSedeAlumno) || ! empty($this->filtroSedeMatricula)) {
            $query->where(function ($userOrMatriculaQuery) {
                if (! empty($this->filtroSedeAlumno)) {
                    $userOrMatriculaQuery->whereIn('matriculas.user_id', function ($userQuery) {
                        $userQuery->select('id')->from('users')->whereIn('sede_id', $this->filtroSedeAlumno);
                    });
                }
                if (! empty($this->filtroSedeMatricula)) {
                    $userOrMatriculaQuery->orWhereHas('horarioMateriaPeriodo.horarioBase.aula.sede', function ($sedeQuery) {
                        $sedeQuery->whereIn('sedes.id', $this->filtroSedeMatricula);
                    });
                }
            });
        }

        return $query;
    }
}
