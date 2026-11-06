<?php

namespace App\Exports;

use Generator;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ConsultaTransaccionesFinanzasExport extends DefaultValueBinder implements FromGenerator, WithCustomValueBinder, WithHeadings
{
    public function __construct(private readonly Builder $consulta) {}

    public function headings(): array
    {
        return ['Origen', 'ID', 'Fecha', 'Referencia', 'Persona', 'Identificación', 'Concepto', 'Método de pago', 'Código de pasarela', 'Punto de pago', 'Estado', 'Valor'];
    }

    public function generator(): Generator
    {
        foreach ($this->consulta->orderBy('fecha')->orderBy('origen')->orderBy('id')->cursor() as $movimiento) {
            yield [
                $movimiento->origen,
                $movimiento->id,
                $movimiento->fecha,
                $movimiento->referencia,
                $movimiento->persona,
                $movimiento->identificacion,
                $movimiento->concepto,
                $movimiento->metodo_pago,
                $movimiento->int_id_forma_pago,
                $movimiento->punto_pago,
                $movimiento->estado,
                $movimiento->valor,
            ];
        }
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), ['D', 'F'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
