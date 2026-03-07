<?php

namespace App\Exports;

use App\Models\Periodo;
use App\Models\Matricula;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class InformeFinalPeriodoExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Periodo $periodo;

    public function __construct(Periodo $periodo)
    {
        $this->periodo = $periodo;
    }

    /**
     * Define los encabezados de las columnas en el archivo Excel.
     * @return array
     */
    public function headings(): array
    {
        return [
            'Identificación del Alumno',
            'Nombre del Alumno',
            'Materia',
            'Carácter Obligatorio',
            'Horario',
            'Aula',
            'Sede',
            'Estado Final',
            'Total Asistencias',
            'Nota Final',
            'Bloqueado',
            'Trasladado',
            'Motivo Reprobación',
        ];
    }

    /**
     * Prepara la consulta a la base de datos para obtener los datos de todo el periodo.
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        // La consulta empieza desde Matricula para garantizar una fila por inscripción, eliminando duplicados.
        return Matricula::query()
            ->where('matriculas.periodo_id', $this->periodo->id)
            ->join('users', 'matriculas.user_id', '=', 'users.id')
            ->join('horarios_materia_periodo', 'matriculas.horario_materia_periodo_id', '=', 'horarios_materia_periodo.id')
            ->join('horarios_base', 'horarios_materia_periodo.horario_base_id', '=', 'horarios_base.id')
            ->join('aulas', 'horarios_base.aula_id', '=', 'aulas.id')
            ->join('sedes', 'aulas.sede_id', '=', 'sedes.id')
            ->join('materia_periodo', 'horarios_materia_periodo.materia_periodo_id', '=', 'materia_periodo.id')
            ->join('materias', 'materia_periodo.materia_id', '=', 'materias.id')
            // Usamos LEFT JOIN para el resultado final, por si aún no ha sido calculado.
            ->leftJoin('materias_aprobada_usuario', function ($join) {
                $join->on('matriculas.user_id', '=', 'materias_aprobada_usuario.user_id')
                    ->on('materia_periodo.id', '=', 'materias_aprobada_usuario.materia_periodo_id');
            })
            // Usamos LEFT JOIN para los traslados
            ->leftJoin('traslados_matricula_log', 'matriculas.id', '=', 'traslados_matricula_log.matricula_id')
            ->select(
                'users.identificacion',
                'users.primer_nombre',
                'users.segundo_nombre',
                'users.primer_apellido',
                'users.segundo_apellido',
                'materias.nombre as nombre_materia',
                'materias.caracter_obligatorio as caracter_obligatorio', // Campo corregido
                DB::raw("CONCAT(horarios_base.dia, ', ', TO_CHAR(horarios_base.hora_inicio, 'HH12:MI AM'), ' - ', TO_CHAR(horarios_base.hora_fin, 'HH12:MI AM')) AS horario_texto"),
                'aulas.nombre as nombre_aula',
                'sedes.nombre as nombre_sede',
                'materias_aprobada_usuario.aprobado',
                'materias_aprobada_usuario.total_asistencias',
                'materias_aprobada_usuario.nota_final',
                'matriculas.bloqueado',
                DB::raw('CASE WHEN traslados_matricula_log.id IS NOT NULL THEN \'Si\' ELSE \'N/A\' END as trasladado'),
                'materias_aprobada_usuario.motivo_reprobacion'
            )
            ->distinct() // Aseguramos que no haya duplicados por múltiples traslados
            ->orderBy('materias.nombre')
            ->orderBy('horario_texto')
            ->orderBy('users.primer_apellido')
            ->orderBy('users.primer_nombre');
    }

    /**
     * Mapea y formatea cada fila de la consulta a las columnas del Excel.
     * @param mixed $resultado El resultado de la consulta para una fila.
     * @return array
     */
    public function map($resultado): array
    {
        return [
            $resultado->identificacion,
            trim($resultado->primer_nombre . ' ' . $resultado->segundo_nombre . ' ' . $resultado->primer_apellido . ' ' . $resultado->segundo_apellido),
            $resultado->nombre_materia,
            $resultado->caracter_obligatorio ? 'Si' : 'No', // Mapeo corregido
            $resultado->horario_texto,
            $resultado->nombre_aula,
            $resultado->nombre_sede,
            $resultado->aprobado === null ? 'N/A' : ($resultado->aprobado ? 'Aprobado' : 'No Aprobado'),
            $resultado->total_asistencias,
            $resultado->nota_final !== null ? number_format($resultado->nota_final, 2) : 'N/A',
            $resultado->bloqueado ? 'Si' : 'N/A',
            $resultado->trasladado,
            $resultado->motivo_reprobacion ?? 'N/A',
        ];
    }
}
