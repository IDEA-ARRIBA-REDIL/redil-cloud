<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HomologacionesMasivasImport implements ToArray, WithHeadingRow
{
    /**
     * @return array
     */
    public function array(array $rows)
    {
        return $rows;
    }
}
