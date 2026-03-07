<?php

namespace App\Exports;

use App\Models\HorarioBase;
use App\Models\Materia;
use Maatwebsite\Excel\Concerns\FromQuery; // Usaremos FromQuery para eficiencia
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable; // Trait para facilitar la descarga

class HorariosMateriaExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable; // Añadir este trait

    protected Materia $materia;

    // Mapa para traducir el número del día a nombre
    protected $diasSemana = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
    ];

    public function __construct(Materia $materia)
    {
        $this->materia = $materia;
    }

    /**
     * Define la consulta base para obtener los horarios de la materia.
     * Carga las relaciones necesarias para evitar consultas N+1.
     */
    public function query()
    {
        return HorarioBase::query()
            ->where('materia_id', $this->materia->id)
            ->with(['aula.sede', 'aula.tipo']) // Precargamos relaciones
            ->orderBy('dia')
            ->orderBy('hora_inicio');
    }

    /**
     * Define los encabezados de las columnas en el archivo Excel.
     */
    public function headings(): array
    {
        return [
            'Día',
            'Hora Inicio',
            'Hora Fin',
            'Sede',
            'Tipo Aula',
            'Aula',
            'Cupos Iniciales',
            'Cupos Límite',
            'Estado',
        ];
    }

    /**
     * Mapea cada objeto HorarioBase a una fila de datos para el Excel.
     *
     * @param HorarioBase $horario
     */
    public function map($horario): array
    {
        return [
            $this->diasSemana[$horario->dia] ?? 'Desconocido', // Traduce el número del día
            $horario->hora_inicio,
            $horario->hora_fin,
            $horario->aula->sede->nombre ?? 'N/A', // Accede a través de relaciones
            $horario->aula->tipo->nombre ?? 'N/A', // Accede a través de relaciones
            $horario->aula->nombre ?? 'N/A',
            $horario->capacidad,
            $horario->capacidad_limite,
            $horario->activo ? 'Activo' : 'Inactivo', // Muestra texto en lugar de 1/0
        ];
    }
}
