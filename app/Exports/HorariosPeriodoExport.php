<?php

namespace App\Exports;

use App\Models\HorarioMateriaPeriodo;
use App\Models\Periodo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class HorariosPeriodoExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $periodo;

    public function __construct(Periodo $periodo)
    {
        $this->periodo = $periodo;
    }

    public function headings(): array
    {
        return [
            'Materia',
            'Horario',
            'Sede',
            'Aula',
            'Tipo de Aula',
            'Estado',
            'Maestro(s)',
            'Alumnos Inscritos',
        ];
    }

    /**
     * Mapea los datos para cada fila del Excel.
     * @param HorarioMateriaPeriodo $horario
     */
    public function map($horario): array
    {
        // === INICIO DE LA CORRECCIÓN ===
        // Usamos map() para iterar sobre la colección de maestros
        // y llamar de forma segura al método nombre() de cada usuario asociado.
        $maestros = $horario->maestros->map(function ($maestro) {
            // Verificamos que la relación user exista para evitar errores
            return $maestro->user ? $maestro->user->nombre(3) : 'Error: Usuario no encontrado';
        })->implode(', ');
        // === FIN DE LA CORRECCIÓN ===

        return [
            $horario->materiaPeriodo->materia->nombre,
            $horario->horarioBase->dia_semana . ' | ' . $horario->horarioBase->hora_inicio_formato . ' - ' . $horario->horarioBase->hora_fin_formato,
            $horario->horarioBase->aula->sede->nombre ?? 'N/A',
            $horario->horarioBase->aula->nombre ?? 'N/A',
            $horario->horarioBase->aula->tipo->nombre ?? 'N/A',
            $horario->habilitado ? 'Si' : 'Inactivo',
            $maestros ?: 'Sin asignar', // Usamos la variable corregida
            $horario->matriculas_de_alumnos_count,
        ];
    }

    /**
     * Define la consulta a la base de datos.
     * La carga anticipada (eager loading) se asegura de que las relaciones
     * 'maestros' y 'user' estén disponibles en el método map() sin consultas adicionales.
     */
    public function query()
    {
        return HorarioMateriaPeriodo::query()
            ->whereHas('materiaPeriodo', function ($query) {
                $query->where('periodo_id', $this->periodo->id);
            })
            ->with([
                'materiaPeriodo.materia',
                'horarioBase.aula.sede',
                'horarioBase.aula.tipo',
                'maestros.user' // <-- Esta línea es clave para cargar la relación Maestro -> User
            ])
            ->withCount('matriculasDeAlumnos');
    }
}
