<?php

namespace Database\Seeders;

use App\Models\CategoriaTema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaTemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        CategoriaTema::firstOrCreate([
            'nombre'=> 'Devocional',
            
        ]);
        CategoriaTema::firstOrCreate([
            'nombre'=> 'Jesús',
            
        ]);

        CategoriaTema::firstOrCreate([
            'nombre'=> 'Espiritu Santo',
            
        ]);

        CategoriaTema::firstOrCreate([
            'nombre'=> 'Alabanza',
            
        ]);

        CategoriaTema::firstOrCreate([
            'nombre'=> 'Parejas',
            
        ]);
    }
}
