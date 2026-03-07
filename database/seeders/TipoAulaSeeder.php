<?php

namespace Database\Seeders;
use App\Models\TipoAula;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoAulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        TipoAula::firstOrCreate(
            ['nombre'=>'Presencial Templo'],
            ['sector'=>false]
        );

        TipoAula::firstOrCreate(
            ['nombre'=>'Presencial Sector'],
            ['sector'=> true]
        );

        TipoAula::firstOrCreate(
            ['nombre'=>'Virtual True'],
             ['sector'=>true]
        );

        TipoAula::firstOrCreate(
            ['nombre'=>'Virtual False'],
             ['sector'=>false]
        );

        TipoAula::firstOrCreate(
            ['nombre'=>'Internacional'],
            ['sector'=>false]
        );

        TipoAula::firstOrCreate(
            ['nombre'=>'Mixta'],
            ['sector'=>false]
        );
    }
}
