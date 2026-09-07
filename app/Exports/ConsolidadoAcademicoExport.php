<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ConsolidadoAcademicoExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected string $escuelaNombre,
        protected string $modo,
        protected string $filtroTipoMaterias,
        protected string $filtroEstadoUsuario,
        protected Collection $items,
        protected Collection $estudiantes,
        protected array $metricas
    ) {}

    public function view(): View
    {
        return view('exports.consolidado-academico-excel', [
            'escuelaNombre' => $this->escuelaNombre,
            'modo' => $this->modo,
            'filtroTipoMaterias' => $this->filtroTipoMaterias,
            'filtroEstadoUsuario' => $this->filtroEstadoUsuario,
            'items' => $this->items,
            'estudiantes' => $this->estudiantes,
            'metricas' => $this->metricas,
        ]);
    }

    public function title(): string
    {
        return 'Consolidado Académico';
    }
}
