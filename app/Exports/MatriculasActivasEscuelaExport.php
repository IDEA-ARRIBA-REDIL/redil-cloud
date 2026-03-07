<?php

namespace App\Exports;

use App\Models\Matricula;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class MatriculasActivasEscuelaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $matriculas;

    // El constructor recibe la colección de matrículas ya filtradas desde el controlador
    public function __construct($matriculas)
    {
        $this->matriculas = $matriculas;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->matriculas;
    }

    /**
     * Define los encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'Alumno',
            'Identificación',
            'Periodo',
            'Materia',
            'Horario',
            'Sede Matrícula',
            'Bloqueado',
            'Fecha Bloqueo',
        ];
    }

    /**
     * Mapea cada objeto Matricula a un array que representa una fila en el Excel.
     * @var Matricula $matricula
     */
    public function map($matricula): array
    {
        // Accedemos a los datos relacionados (asegúrate de que estén cargados con Eager Loading en el controlador)
        $alumnoNombre = optional($matricula->user)->nombre(4) ?? 'N/A'; // Usamos optional() por seguridad
        $alumnoId = optional($matricula->user)->identificacion ?? 'N/A';
        $periodoNombre = optional($matricula->periodo)->nombre ?? 'N/A';
        $materiaNombre = optional($matricula->horarioMateriaPeriodo->materiaPeriodo->materia)->nombre ?? 'N/A';

        // Construimos la descripción del horario
        $horarioBase = optional($matricula->horarioMateriaPeriodo)->horarioBase;
        $horarioDesc = 'N/A';
        if ($horarioBase) {
            $horarioDesc = $horarioBase->dia_semana . ' ' . $horarioBase->hora_inicio_formato .
                ' (' . (optional($horarioBase->aula)->nombre ?? 'Aula N/A') . ')';
        }

        $sedeNombre = optional($matricula->sede)->nombre ?? 'N/A'; // Asume relación directa matricula->sede
        $bloqueado = $matricula->bloqueado ? 'Sí' : 'No';
        $fechaBloqueo = $matricula->fecha_bloqueo ? Carbon::parse($matricula->fecha_bloqueo)->format('Y-m-d H:i') : '';

        return [
            $alumnoNombre,
            $alumnoId,
            $periodoNombre,
            $materiaNombre,
            $horarioDesc,
            $sedeNombre,
            $bloqueado,
            $fechaBloqueo,
        ];
    }
}
