<?php

namespace Database\Seeders;

use App\Models\TiempoConDios;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TiempoConDiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

       TiempoConDios::firstOrCreate([
          'user_id' => 1,
          'fecha' => '2025-03-17',
        ]);

     /*     TiempoConDios::firstOrCreate([
          'user_id' => 1,
          'fecha' => '2025-03-19',
        ]);*/


        TiempoConDios::firstOrCreate([
          'user_id' => 1,
          'fecha' => '2025-03-20',
        ]);



        /* TiempoConDios::firstOrCreate([
          'user_id' => 1,
          'fecha' => '2025-03-13',
        ]);

        TiempoConDios::firstOrCreate([
          'user_id' => 1,
          'fecha' => '2025-03-12',
        ]);*/

        TiempoConDios::firstOrCreate([
          'user_id' => 1,
          'fecha' => '2025-03-11',
        ]);

        for ($i=1; $i < 11; $i++) {
          TiempoConDios::firstOrCreate([
            'user_id' => 1,
            'fecha' => '2025-03-'.$i,
          ]);
        }

        for ($i=1; $i < 29; $i++) {
          TiempoConDios::firstOrCreate([
            'user_id' => 1,
            'fecha' => '2025-02-'.$i,
          ]);
        }


        for ($i=1; $i < 32; $i++) {
          TiempoConDios::firstOrCreate([
            'user_id' => 1,
            'fecha' => '2025-01-'.$i,
          ]);
        }



    }
}
