<?php

namespace Database\Seeders;

use App\Models\EntidadRelacionada;
use Illuminate\Database\Seeder;

class EntidadRelacionadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, we ensure the default "Iglesia" exists.
        // We use 'nombre' to avoid manual ID insertion which can break sequences in Postgres.
        EntidadRelacionada::firstOrCreate(['nombre' => 'IMVE']);

        EntidadRelacionada::firstOrCreate(['nombre' => 'LMVE']);

        EntidadRelacionada::firstOrCreate(['nombre' => 'CEV']);
    }
}
