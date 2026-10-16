<?php

namespace App\Exports;

use App\Models\Informe;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MegaInformeExport implements FromView, ShouldAutoSize
{
    public function __construct(
        public readonly string $tablaHtml,
        public readonly Informe $informe
    ) {}

    public function view(): View
    {
        return view('contenido.paginas.informes-personalizados.excel.mega-informe', [
            'tablaHtml' => $this->tablaHtml,
            'informe' => $this->informe,
        ]);
    }
}
