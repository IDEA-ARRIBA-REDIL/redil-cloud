<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


use App\Models\Zona;

class ZonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zona1 = Zona::create([
            'nombre' => 'Zona localidad Kennedy',
        ]);

        $zona1->sedes()->attach([2]);
        $zona1->localidades()->attach([8]);

        $zona2 = Zona::create([
            'nombre' => 'Zona localidad Fontibón y Engativá',
        ]);

        $zona2->sedes()->attach([2]);
        $zona2->localidades()->attach([9,10]);

        $zona3 = Zona::create([
            'nombre' => 'Zona localidad Bosa',
        ]);

        $zona3->sedes()->attach([2]);
        $zona3->localidades()->attach([7]);

        $zona4 = Zona::create([
            'nombre' => 'Zona sedes Bogotá',
        ]);

        $zona4->sedes()->attach([29,28,30,17,14,22,210,24,31,45,44]);

        $zona5 = Zona::create([
            'nombre' => 'Zona sedes Medellín',
        ]);

        $zona5->sedes()->attach([309]);

        $zona6= Zona::create([
            'nombre' => 'Zona auditorio principal',
        ]);

        $zona6->sedes()->attach([2]);


    }
}
