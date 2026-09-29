<?php

namespace App\Exports;

use App\Models\MateriaPeriodo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InformeMateriaPorSedeExport implements WithMultipleSheets
{
    public function __construct(private MateriaPeriodo $materia, private Collection $horarios) {}

    public function sheets(): array
    {
        return $this->horarios->map(fn ($horario): InformeHorarioMateriaExport => new InformeHorarioMateriaExport($this->materia, $horario))->all();
    }
}
