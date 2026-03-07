<?php

namespace App\Exports;

use App\Models\Actividad;
use App\Models\Inscripcion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AsistenciasActividadExport implements FromCollection, WithHeadings, WithMapping
{
    protected Actividad $actividad;
    protected array $fechasActividad;

    /**
     * El constructor recibe la Actividad y calcula el rango de fechas del evento.
     */
    public function __construct(Actividad $actividad)
    {
        $this->actividad = $actividad;

        $this->fechasActividad = [];
        if ($actividad->fecha_inicio && $actividad->fecha_finalizacion) {
            $periodo = CarbonPeriod::create($actividad->fecha_inicio, $actividad->fecha_finalizacion);
            foreach ($periodo as $date) {
                $this->fechasActividad[] = $date->format('Y-m-d');
            }
        }
    }

    /**
     * Prepara la colección de datos base: todas las inscripciones de la actividad.
     * Precargamos las relaciones para un rendimiento óptimo.
     */
    public function collection()
    {
        return $this->actividad->inscripciones()
            ->withCount('asistencias') // Contamos el total de asistencias eficientemente
            ->with(['user', 'compra', 'asistencias']) // Precargamos las relaciones que usaremos
            ->get();
    }

    /**
     * Define la fila de encabezados del Excel.
     * Será dinámica según la duración de la actividad.
     */
    public function headings(): array
    {
        $encabezados = [
            'Participante',
            'Identificación',
            'Correo Electrónico',
            'Teléfono',
            'Total Asistencias',
        ];

        // Añadimos una columna por cada día de la actividad
        foreach ($this->fechasActividad as $fecha) {
            $encabezados[] = Carbon::parse($fecha)->isoFormat('dddd DD-MMM'); // E.g., "lunes 25-Ago"
        }

        return $encabezados;
    }

    /**
     * Mapea cada inscripción a una fila de datos para el Excel.
     *
     * @param \App\Models\Inscripcion $inscripcion
     */
    public function map($inscripcion): array
    {
        // 1. Obtenemos el nombre y la identificación del participante (usuario o invitado)
        $nombre = $inscripcion->user?->nombre(3) ?? $inscripcion->compra?->nombre_completo_comprador ?? 'Invitado';
        $identificacion = $inscripcion->user?->identificacion ?? $inscripcion->compra?->identificacion_comprador ?? 'N/A';
        $email = $inscripcion->user?->email ?? $inscripcion->compra?->email_comprador ?? 'N/A';
        $telefono = $inscripcion->user?->telefono_movil ?? $inscripcion->compra?->telefono_comprador ?? 'N/A';

        // 2. Creamos un mapa de las fechas en las que el participante SÍ asistió
        $asistenciasDelParticipante = $inscripcion->asistencias
            ->pluck('fecha')
            ->map(fn($fecha) => Carbon::parse($fecha)->format('Y-m-d'))
            ->flip();

        // 3. Construimos la fila del Excel
        $fila = [
            $nombre,
            $identificacion,
            $email,
            $telefono,
            $inscripcion->asistencias_count, // Usamos el conteo precargado
        ];

        // 4. Recorremos todas las fechas del evento y ponemos "Sí" o "No"
        foreach ($this->fechasActividad as $fecha) {
            $fila[] = isset($asistenciasDelParticipante[$fecha]) ? 'Sí' : 'No';
        }

        return $fila;
    }
}
