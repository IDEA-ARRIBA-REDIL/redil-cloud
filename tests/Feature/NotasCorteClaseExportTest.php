<?php

namespace Tests\Feature;

use App\Exports\NotasCorteClaseExport;
use App\Models\CortePeriodo;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use Illuminate\Support\Collection;
use Tests\TestCase;

class NotasCorteClaseExportTest extends TestCase
{
    public function test_it_creates_headings_for_the_items_of_a_cut(): void
    {
        $corte = new CortePeriodo;
        $corte->setAttribute('id', 5);
        $corte->setRelation('corteEscuela', null);
        $items = new Collection([
            new ItemCorteMateriaPeriodo([
                'corte_periodo_id' => 5,
                'nombre' => 'Examen final',
                'porcentaje' => 70,
            ]),
            new ItemCorteMateriaPeriodo([
                'corte_periodo_id' => 5,
                'nombre' => 'Taller',
                'porcentaje' => 30,
            ]),
        ]);
        $exportacion = new NotasCorteClaseExport(new HorarioMateriaPeriodo, $corte, 3.0, $items);

        $this->assertSame([
            'Identificación',
            'Alumno',
            'Examen final (70%)',
            'Taller (30%)',
            'Promedio Corte',
            'Promedio final',
            '¿Va aprobando?',
            'Estado',
        ], $exportacion->headings());
    }
}
