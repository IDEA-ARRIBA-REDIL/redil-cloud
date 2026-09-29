<?php

namespace App\Exports;

use App\Models\HorarioMateriaPeriodo;
use App\Models\MateriaAprobadaUsuario;
use App\Models\MateriaPeriodo;
use App\Models\Matricula;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InformeHorarioMateriaExport extends DefaultValueBinder implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithStrictNullComparison, WithStyles, WithTitle
{
    public function __construct(private MateriaPeriodo $materia, private HorarioMateriaPeriodo $horario) {}

    public function title(): string
    {
        return 'Horario '.$this->horario->id;
    }

    public function headings(): array
    {
        return ['Identificación', 'Alumno', 'Periodo', 'Materia', 'Sede del horario', 'Horario', 'Aula', 'Estado final', 'Nota acumulada final', 'Asistencias finales', 'Matrícula bloqueada', 'Motivo de reprobación'];
    }

    public function collection(): Collection
    {
        $this->materia->loadMissing(['materia', 'periodo']);
        $matriculas = Matricula::query()->where('periodo_id', $this->materia->periodo_id)
            ->where('horario_materia_periodo_id', $this->horario->id)
            ->whereNotIn('estado_pago_matricula', ['anulada', 'rechazada'])
            ->with(['user' => fn ($consulta) => $consulta->withTrashed()])->get()->groupBy('user_id');
        $resultados = MateriaAprobadaUsuario::query()->where('periodo_id', $this->materia->periodo_id)
            ->where('materia_periodo_id', $this->materia->id)->whereIn('user_id', $matriculas->keys())
            ->orderBy('id')->get()->keyBy('user_id');
        $base = $this->horario->horarioBase;

        return $matriculas->map(function (Collection $inscripciones, int $usuarioId) use ($resultados, $base): array {
            $usuario = $inscripciones->first()->user;
            $resultado = $resultados->get($usuarioId);
            $definitivo = $resultado && in_array($resultado->aprobado, [0, 1], true);

            return [
                (string) ($usuario?->identificacion ?? ''),
                $usuario ? trim(implode(' ', array_filter([$usuario->primer_nombre, $usuario->segundo_nombre, $usuario->primer_apellido, $usuario->segundo_apellido]))) : 'Alumno no disponible',
                $this->materia->periodo->nombre,
                $this->materia->materia?->nombre ?? 'Materia no disponible',
                $base?->aula?->sede?->nombre ?? 'Sede no disponible',
                $base ? $base->dia_semana.' '.$base->hora_inicio_formato.' - '.$base->hora_fin_formato : 'Horario no disponible',
                $base?->aula?->nombre ?? 'Aula no disponible',
                $definitivo ? ($resultado->esAprobado() ? 'Aprobado' : 'Reprobado') : 'Sin resultado definitivo',
                $definitivo && $resultado->nota_final !== null ? (float) $resultado->nota_final : null,
                $definitivo && $resultado->total_asistencias !== null ? (int) $resultado->total_asistencias : null,
                $inscripciones->contains('bloqueado', true) ? 'Sí' : 'No',
                $definitivo ? ($resultado->motivo_reprobacion ?? '') : '',
            ];
        })->sortBy(fn (array $fila): string => $fila[1])->values();
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return ['I' => '0.00', 'J' => '0'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [1 => ['font' => ['bold' => true]]];
    }
}
