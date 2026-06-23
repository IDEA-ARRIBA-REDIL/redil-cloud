<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InformeObrerosExport implements FromView
{
    public function __construct(public readonly string $tablaCompleta) {}

    public function view(): View
    {
        return view('contenido.paginas.informes-personalizados.excel.informe-obreros', [
            'tablaCompleta' => $this->tablaCompleta,
        ]);
    }
}
