<?php

namespace Database\Seeders;

use App\Models\TemaCategoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemaCategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        TemaCategoria::firstOrCreate([
          'tema_id'=> '1',
          'categoria_tema_id'=> '1'
        ]);

        TemaCategoria::firstOrCreate([
          'tema_id'=> '1',
          'categoria_tema_id'=> '2'
        ]);

        TemaCategoria::firstOrCreate([
          'tema_id'=> '2',
          'categoria_tema_id'=> '1'
        ]);

        TemaCategoria::firstOrCreate([
          'tema_id'=> '2',
          'categoria_tema_id'=> '3'
        ]);

        TemaCategoria::firstOrCreate([
          'tema_id'=> '3',
          'categoria_tema_id'=> '2'
        ]);

        TemaCategoria::firstOrCreate([
          'tema_id'=> '3',
          'categoria_tema_id'=> '3'
      ]);

    }
}
