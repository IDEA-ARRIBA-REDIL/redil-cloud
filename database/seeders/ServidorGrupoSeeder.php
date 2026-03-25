<?php

namespace Database\Seeders;

use App\Models\ServidorGrupo;
use Illuminate\Database\Seeder;

class ServidorGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ServidorGrupo::firstOrCreate([
            'grupo_id' => 2,
            'user_id' => 6,
        ]);

        ServidorGrupo::firstOrCreate([
            'grupo_id' => 2,
            'user_id' => 3,
        ]);
    }
}
