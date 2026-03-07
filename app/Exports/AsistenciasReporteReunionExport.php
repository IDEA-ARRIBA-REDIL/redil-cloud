<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AsistenciasReporteReunionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $reunionId;

    public function __construct(int $reunionId)
    {
        $this->reunionId = $reunionId;
    }

    /**
     * En lugar de query(), usamos collection().
     * Aquí construimos la consulta manualmente con DB::table().
     */
    public function collection()
    {
        return DB::table('asistencia_reuniones as ar')
            // Unimos con la tabla de usuarios para obtener los datos del ASISTENTE
            // Usamos un alias 'asistente' para no confundirlo con la otra unión.
            ->leftJoin('users as asistente', 'ar.user_id', '=', 'asistente.id')

            // Unimos DE NUEVO con la tabla de usuarios para los datos del AUTOR
            // Usamos un alias diferente, 'autor'.
            ->leftJoin('users as autor', 'ar.autor_creacion_asistencia_id', '=', 'autor.id')

            ->leftJoin('tipo_identificaciones as ti', 'asistente.tipo_identificacion_id', '=', 'ti.id')

            // Filtramos por el ID de la reunión
            ->where('ar.reporte_reunion_id', $this->reunionId)

            // Seleccionamos explícitamente las columnas que necesitamos, con alias
            ->select(
                'asistente.primer_nombre as asistente_p_nombre',
                'asistente.segundo_nombre as asistente_s_nombre',
                'asistente.primer_apellido as asistente_p_apellido',
                'asistente.segundo_apellido as asistente_s_apellido',
                'asistente.email as asistente_email',
                'ti.nombre as tipo_identificacion_nombre',
                'asistente.identificacion',
                'autor.primer_nombre as autor_p_nombre',
                'autor.segundo_nombre as autor_s_nombre',
                'autor.primer_apellido as autor_p_apellido',
                'autor.segundo_apellido as autor_s_apellido'
            )
            ->get();
    }

    /**
     * Los encabezados no cambian.
     */
    public function headings(): array
    {
        return [
            'Nombre Completo',
            'Email',
            'Tipo de Identificación',
            'Número de Identificación',
            'Asistencia Registrada por',
        ];
    }

    /**
     * El mapeo ahora trabaja con un objeto estándar, no un modelo Eloquent.
     * Accedemos a las propiedades usando los alias que definimos en select().
     *
     * @param mixed $row
     */
    public function map($row): array
    {
        // Construimos los nombres completos manualmente a partir de las columnas seleccionadas
        $nombreAsistente = trim("{$row->asistente_p_nombre} {$row->asistente_s_nombre} {$row->asistente_p_apellido} {$row->asistente_s_apellido}");
        $nombreAutor = trim("{$row->autor_p_nombre} {$row->autor_s_nombre} {$row->autor_p_apellido} {$row->autor_s_apellido}");

        return [
            $nombreAsistente ?: 'Usuario no encontrado',
            $row->asistente_email ?? 'N/A',
            $row->tipo_identificacion_nombre ?? 'N/A',
            $row->identificacion ?? 'N/A',
            $nombreAutor ?: 'No indicado',
        ];
    }
}
