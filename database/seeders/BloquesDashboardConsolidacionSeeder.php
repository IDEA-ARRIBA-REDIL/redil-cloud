<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BloqueDashboardConsolidacion;

class BloquesDashboardConsolidacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un bloque por defecto si no existen
       /* if (BloqueDashboardConsolidacion::count() == 0) {
            BloqueDashboardConsolidacion::firstOrCreate(['nombre' => 'Bloque General']);
        }*/

        $bloque1 = BloqueDashboardConsolidacion::firstOrCreate(['nombre' => 'Auditorio principal']);
        if($bloque1->wasRecentlyCreated) {
            $bloque1->sedes()->attach(2);
        }

        $bloque2 = BloqueDashboardConsolidacion::firstOrCreate(['nombre' => 'Sedes Bogotá']);
        if($bloque2->wasRecentlyCreated) {
            $bloque2->sedes()->attach([29,28,30,17,14,22,210,24,31,45,44]);
        }

        $bloque3 = BloqueDashboardConsolidacion::firstOrCreate(['nombre' => 'Sedes Nacionales']);
        if($bloque3->wasRecentlyCreated) {
            $bloque3->sedes()->attach([309,276,408]);
        }


    }
}
