<?php

namespace Database\Seeders;

use App\Models\TipoCampoTiempoConDios;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoCampoTiempoConDiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      //1
      TipoCampoTiempoConDios::firstOrCreate([
        'nombre'=>'textfield',
        'es_input' => true
      ]);

      //2
      TipoCampoTiempoConDios::firstOrCreate([
        'nombre'=>'html',
        'es_input' => false
      ]);

      //3
      TipoCampoTiempoConDios::firstOrCreate([
        'nombre'=>'imagen',
        'es_input' => false
      ]);

      //4
      TipoCampoTiempoConDios::firstOrCreate([
        'nombre'=>'reproductor',
        'es_input' => false
      ]);

      // 5
      TipoCampoTiempoConDios::firstOrCreate([
        'nombre'=>'biblia',
        'es_input' => true
      ]);
    }
}
