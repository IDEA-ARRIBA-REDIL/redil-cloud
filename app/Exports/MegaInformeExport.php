<?php

namespace App\Exports;

use App\Models\InformePersonalizado;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class MegaInformeExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(
        public readonly string $tablaHtml,
        public readonly InformePersonalizado $informe
    ) {}

    public function view(): View
    {
        return view('contenido.paginas.informes-personalizados.excel.mega-informe', [
            'tablaHtml' => $this->tablaHtml,
            'informe' => $this->informe,
        ]);
    }

    public function title(): string
    {
        return mb_substr($this->informe->nombre, 0, 31);
    }
}
