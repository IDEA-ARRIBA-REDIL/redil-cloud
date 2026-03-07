<?php

namespace Database\Seeders;

use App\Models\Aula;
use Illuminate\Database\Seeder;

class AulaSeeder extends Seeder
{
    public function run(): void
    {
        Aula::firstOrCreate(['nombre' => 'Aula 101 ', 'sede_id' => 1], ['tipo_aula_id'=>1 ,'activo'=>true]);
        Aula::firstOrCreate(['nombre' => 'Aula 201 ', 'sede_id' => 1], ['tipo_aula_id'=>2, 'activo'=>true]);
        Aula::firstOrCreate(['nombre' => 'Aula General Local 1', 'sede_id' => 2], ['tipo_aula_id'=>1, 'activo'=>true]);
        Aula::firstOrCreate(['nombre' => 'Aula General Local 2 ', 'sede_id' => 2], ['tipo_aula_id'=>2, 'activo'=>true]);
    }
}
