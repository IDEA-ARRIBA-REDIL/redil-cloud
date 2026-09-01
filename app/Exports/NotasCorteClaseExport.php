<?php

namespace App\Exports;

use App\Models\AlumnoRespuestaItem;
use App\Models\CortePeriodo;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\MatriculaHorarioMateriaPeriodo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NotasCorteClaseExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    private ?Collection $items;

    public function __construct(
        private readonly HorarioMateriaPeriodo $horarioAsignado,
        private readonly CortePeriodo $cortePeriodo,
        private readonly float $notaMinimaAprobatoria,
        ?Collection $items = null,
    ) {
        $this->items = $items;
    }

    /**
     * @return Collection<int, array<int, string>>
     */
    public function collection(): Collection
    {
        $itemsDeLaClase = $this->items();
        $itemsDelCorte = $itemsDeLaClase
            ->where('corte_periodo_id', $this->cortePeriodo->id)
            ->values();
        $cortesDelPeriodo = CortePeriodo::query()
            ->where('periodo_id', $this->horarioAsignado->materiaPeriodo->periodo_id)
            ->with('corteEscuela:id,nombre,orden')
            ->select('cortes_periodo.*')
            ->join('cortes_escuela', 'cortes_periodo.corte_escuela_id', '=', 'cortes_escuela.id')
            ->orderBy('cortes_escuela.orden')
            ->get();
        $estadosAcademicos = MatriculaHorarioMateriaPeriodo::query()
            ->where('horario_materia_periodo_id', $this->horarioAsignado->id)
            ->with([
                'user:id,primer_nombre,segundo_nombre,primer_apellido,segundo_apellido,identificacion',
                'matricula:id,bloqueado',
            ])
            ->get()
            ->filter(fn (MatriculaHorarioMateriaPeriodo $estado) => $estado->user && $estado->matricula)
            ->sortBy(fn (MatriculaHorarioMateriaPeriodo $estado) => $estado->user->nombre(4));
        $notasPorAlumno = AlumnoRespuestaItem::query()
            ->whereIn('user_id', $estadosAcademicos->pluck('user_id'))
            ->whereIn('item_corte_materia_periodo_id', $itemsDeLaClase->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $notas) => $notas->pluck('nota_obtenida', 'item_corte_materia_periodo_id'));
        $periodoFinalizado = $this->horarioAsignado->materiaPeriodo->periodo->fecha_fin->isPast();

        return $estadosAcademicos->map(function (MatriculaHorarioMateriaPeriodo $estado) use ($itemsDelCorte, $itemsDeLaClase, $cortesDelPeriodo, $notasPorAlumno, $periodoFinalizado): array {
            $notasDelAlumno = $notasPorAlumno->get($estado->user_id, collect());
            $promedioCorte = $this->calcularPromedioCorte($itemsDelCorte, $notasDelAlumno);
            $promedioFinal = $cortesDelPeriodo->sum(function (CortePeriodo $corte) use ($itemsDeLaClase, $notasDelAlumno): float {
                $itemsDelCorte = $itemsDeLaClase->where('corte_periodo_id', $corte->id);

                return $this->calcularPromedioCorte($itemsDelCorte, $notasDelAlumno) * ((float) $corte->porcentaje / 100);
            });
            $promedioFinal = round($promedioFinal, 2);
            $haAprobado = ! $estado->matricula->bloqueado && $promedioFinal >= $this->notaMinimaAprobatoria;
            $estadoMateria = $this->estadoMateria($estado->matricula->bloqueado, $periodoFinalizado, $haAprobado);
            $notasItems = $itemsDelCorte->map(function (ItemCorteMateriaPeriodo $item) use ($notasDelAlumno): string {
                $nota = $notasDelAlumno->get($item->id);

                return $nota === null ? 'Sin calificar' : number_format((float) $nota, 2);
            });

            return [
                $estado->user->identificacion ?? '',
                trim($estado->user->nombre(4)),
                ...$notasItems,
                number_format($promedioCorte, 2),
                number_format($promedioFinal, 2),
                $haAprobado ? 'Sí' : 'No',
                $estadoMateria,
            ];
        });
    }

    public function headings(): array
    {
        $encabezados = [
            'Identificación',
            'Alumno',
        ];

        foreach ($this->items()->where('corte_periodo_id', $this->cortePeriodo->id) as $item) {
            $encabezados[] = $item->nombre.' ('.number_format((float) $item->porcentaje, 0).'%)';
        }

        return [
            ...$encabezados,
            'Promedio '.$this->nombreCorte(),
            'Promedio final',
            '¿Va aprobando?',
            'Estado',
        ];
    }

    private function items(): Collection
    {
        return $this->items ??= ItemCorteMateriaPeriodo::query()
            ->where('horario_materia_periodo_id', $this->horarioAsignado->id)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    private function calcularPromedioCorte(Collection $items, Collection $notas): float
    {
        return round($items->sum(function (ItemCorteMateriaPeriodo $item) use ($notas): float {
            $nota = $notas->get($item->id);

            if ($nota === null || ! is_numeric($item->porcentaje)) {
                return 0.0;
            }

            return (float) $nota * ((float) $item->porcentaje / 100);
        }), 2);
    }

    private function estadoMateria(bool $estaBloqueado, bool $periodoFinalizado, bool $haAprobado): string
    {
        if ($estaBloqueado) {
            return 'Bloqueado';
        }

        if ($periodoFinalizado) {
            return $haAprobado ? 'Aprobado' : 'Reprobado';
        }

        return $haAprobado ? 'Aprobando' : 'Cursando';
    }

    private function nombreCorte(): string
    {
        return $this->cortePeriodo->corteEscuela?->nombre ?? 'Corte';
    }
}
