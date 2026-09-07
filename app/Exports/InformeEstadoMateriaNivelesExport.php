<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class InformeEstadoMateriaNivelesExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected string $escuelaNombre,
        protected string $itemNombre,
        protected string $modo,
        protected ?string $rangoFechas,
        protected string $filtroEstadoAcademico,
        protected string $filtroTipoRegistro,
        protected string $filtroEstadoUsuario,
        protected mixed $itemActivo,
        protected Collection $estudiantes,
        protected array $metricas
    ) {}

    public function view(): View
    {
        return view('exports.informe-estado-materia-niveles-excel', [
            'escuelaNombre' => $this->escuelaNombre,
            'itemNombre' => $this->itemNombre,
            'modo' => $this->modo,
            'rangoFechas' => $this->rangoFechas,
            'filtroEstadoAcademico' => $this->filtroEstadoAcademico,
            'filtroTipoRegistro' => $this->filtroTipoRegistro,
            'filtroEstadoUsuario' => $this->filtroEstadoUsuario,
            'itemActivo' => $this->itemActivo,
            'estudiantes' => $this->estudiantes,
            'metricas' => $this->metricas,
        ]);
    }

    public function title(): string
    {
        return mb_substr($this->itemNombre, 0, 30);
    }
}
