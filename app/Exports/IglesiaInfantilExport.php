<?php

namespace App\Exports;

use App\Models\RegistroIglesiaInfantil;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IglesiaInfantilExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected int $reporteReunionId) {}

    /** 1. Traer todos los registros del reporte indicado */
    public function collection()
    {
        return RegistroIglesiaInfantil::with([
            'menor',
            'adultoIngreso',
            'adultoRetiro',
            'servidorIngreso',
            'servidorRetiro',
            'salon',
            'estacion',
            'reporteReunion.reunion',
        ])->where('reporte_reunion_id', $this->reporteReunionId)
            ->orderBy('hora_entrada')
            ->get();
    }

    /** 2. Encabezados de las columnas */
    public function headings(): array
    {
        return [
            'Fecha',
            'Reunión',
            'Nombre Menor',
            'Edad',
            'Adulto que registró',
            'Adulto Retiro',
            'Servidor Ingreso',
            'Servidor Retiro',
            'Salón',
            'Estación',
            'Indicaciones Médicas',
            'Código Retiro',
            'Hora Entrada',
            'Hora Entrega',
            'Estado',
        ];
    }

    /** 3. Mapeo de cada fila */
    public function map($registro): array
    {
        $edadMenor = $registro->menor?->fecha_nacimiento
            ? Carbon::parse($registro->menor->fecha_nacimiento)->age
            : '—';

        return [
            Carbon::parse($registro->fecha)->format('d/m/Y'),
            $registro->reporteReunion?->reunion?->nombre ?? '—',
            $registro->menor?->nombre(3) ?? '—',
            $edadMenor,
            $registro->adultoIngreso?->nombre(3) ?? '—',
            $registro->adultoRetiro?->nombre(3) ?? '—',
            $registro->servidorIngreso?->nombre(3) ?? '—',
            $registro->servidorRetiro?->nombre(3) ?? '—',
            $registro->salon?->nombre ?? '—',
            $registro->estacion?->nombre ?? '—',
            $registro->indicaciones_medicas ?? '—',
            $registro->codigo_retiro,
            $registro->hora_entrada,
            $registro->hora_entrega ?? '—',
            $registro->estaEnCustodia() ? 'En custodia' : 'Entregado',
        ];
    }

    /** 4. Estilos: encabezados en negrita */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
