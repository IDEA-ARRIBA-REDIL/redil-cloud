<?php

namespace App\Exports;

use App\Models\NovedadActividad;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NovedadesExport implements FromView, ShouldAutoSize
{
    public function __construct(
        public ?string $estado = null,
        public ?int $actividadId = null,
        public ?int $tipoNovedadId = null,
        public ?string $desde = null,
        public ?string $hasta = null,
        public ?string $busqueda = null
    ) {}

    public function view(): View
    {
        $novedades = NovedadActividad::query()
            ->with(['actividad', 'tipoNovedad', 'materia', 'respondidoPor'])
            ->estado($this->estado)
            ->actividad($this->actividadId)
            ->tipoNovedad($this->tipoNovedadId)
            ->fecha($this->desde, $this->hasta)
            ->buscar($this->busqueda)
            ->orderBy('id', 'desc')
            ->get();

        return view('exports.novedades-excel', [
            'novedades' => $novedades,
        ]);
    }
}
