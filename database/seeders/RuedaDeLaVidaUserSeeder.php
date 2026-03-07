<?php

namespace Database\Seeders;

use App\Models\RuedaDeLaVidaUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RuedaDeLaVidaUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        RuedaDeLaVidaUser::firstOrCreate([
            'usuario_id' => '1',
            'promedio_general' => '5.4',
            'fecha' => '2025-02-19'

        ]);
    }
}
