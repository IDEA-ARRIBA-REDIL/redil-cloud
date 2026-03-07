<?php

namespace Database\Seeders;

use App\Models\TagGeneral;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagGeneralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         TagGeneral::firstOrCreate([
            'nombre'=> 'Escuelas',
           
        ]);
         //
         TagGeneral::firstOrCreate([
            'nombre'=> 'Encuentro',
           
        ]);

         //
         TagGeneral::firstOrCreate([
            'nombre'=> 'Capacitacion',
           
        ]);

         //
         TagGeneral::firstOrCreate([
            'nombre'=> 'Manantial Kids',
           
        ]);

          //
         TagGeneral::firstOrCreate([
            'nombre'=> 'Ayunos',
           
        ]);
    }
}
