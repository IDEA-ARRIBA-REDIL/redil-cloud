<?php

namespace App\Exports;

use App\Models\MateriaPeriodo;
use App\Models\MateriaAprobadaUsuario;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class InformeFinalMateriaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected MateriaPeriodo $materiaPeriodo;

    /**
     * El constructor recibe la MateriaPeriodo para la cual se generará el reporte.
     * @param MateriaPeriodo $materiaPeriodo La materia del periodo para el informe.
     */
    public function __construct(MateriaPeriodo $materiaPeriodo)
    {
        $this->materiaPeriodo = $materiaPeriodo;
    }

    /**
     * Define los encabezados de las columnas en el archivo Excel.
     * Se añaden las columnas de identificación, estado de bloqueo, traslado y motivo de reprobación.
     * @return array
     */
    public function headings(): array
    {
        return [
            'Identificación del Alumno',
            'Nombre del Alumno',
            'Materia',
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
     * Prepara la consulta a la base de datos para obtener los datos.
     * Se modifica la consulta para incluir la identificación, el estado de bloqueo,
     * el motivo de reprobación y una subconsulta para verificar si el alumno fue trasladado a este horario.
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        // Empezamos desde los resultados finales y unimos las tablas necesarias.
        return MateriaAprobadaUsuario::query()
            ->where('materias_aprobada_usuario.materia_periodo_id', $this->materiaPeriodo->id)
            ->join('users', 'materias_aprobada_usuario.user_id', '=', 'users.id')
            ->join('matriculas', function ($join) {
                $join->on('materias_aprobada_usuario.user_id', '=', 'matriculas.user_id')
                    ->on('materias_aprobada_usuario.periodo_id', '=', 'matriculas.periodo_id');
            })
            ->join('horarios_materia_periodo', 'matriculas.horario_materia_periodo_id', '=', 'horarios_materia_periodo.id')
            ->join('horarios_base', 'horarios_materia_periodo.horario_base_id', '=', 'horarios_base.id')
            ->join('aulas', 'horarios_base.aula_id', '=', 'aulas.id')
            ->join('sedes', 'aulas.sede_id', '=', 'sedes.id')
            ->where('horarios_materia_periodo.materia_periodo_id', $this->materiaPeriodo->id)
            ->select(
                'users.identificacion', // Columna de identificación del alumno
                'users.primer_nombre',
                'users.segundo_nombre',
                'users.primer_apellido',
                'users.segundo_apellido',
                DB::raw("CONCAT(horarios_base.dia, ', ', TO_CHAR(horarios_base.hora_inicio, 'HH12:MI AM'), ' - ', TO_CHAR(horarios_base.hora_fin, 'HH12:MI AM')) AS horario_texto"),
                'aulas.nombre as nombre_aula',
                'sedes.nombre as nombre_sede',
                'materias_aprobada_usuario.aprobado',
                'materias_aprobada_usuario.total_asistencias',
                'materias_aprobada_usuario.nota_final',
                'matriculas.bloqueado', // Columna para saber si la matrícula está bloqueada
                'materias_aprobada_usuario.motivo_reprobacion', // Columna con el motivo de la reprobación
                // Subconsulta corregida para verificar si el alumno fue trasladado a este horario específico
                DB::raw('(CASE WHEN EXISTS (SELECT 1 FROM traslados_matricula_log WHERE traslados_matricula_log.user_id = users.id AND traslados_matricula_log.destino_horario_id = horarios_materia_periodo.id) THEN \'Si\' ELSE \'N/A\' END) as trasladado')
            )
            ->orderBy('users.primer_apellido')
            ->orderBy('users.primer_nombre');
    }

    /**
     * Mapea y formatea cada fila de la consulta a las columnas del Excel.
     * Se ajusta el mapeo para incluir los nuevos campos en el orden definido en los encabezados.
     * @param mixed $resultado El resultado de la consulta para una fila.
     * @return array
     */
    public function map($resultado): array
    {
        return [
            $resultado->identificacion,
            trim($resultado->primer_nombre . ' ' . $resultado->segundo_nombre . ' ' . $resultado->primer_apellido . ' ' . $resultado->segundo_apellido),
            $this->materiaPeriodo->materia->nombre,
            $resultado->horario_texto,
            $resultado->nombre_aula,
            $resultado->nombre_sede,
            $resultado->aprobado ? 'Aprobado' : 'No Aprobado',
            $resultado->total_asistencias,
            number_format($resultado->nota_final, 2),
            $resultado->bloqueado ? 'Si' : 'N/A',
            $resultado->trasladado,
            $resultado->motivo_reprobacion ?? 'N/A',
        ];
    }
}
