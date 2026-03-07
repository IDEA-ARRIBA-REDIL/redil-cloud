<?php

namespace Database\Seeders;

use App\Models\Ofrenda;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfrendaSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $ofrenda = Ofrenda::firstOrCreate(
      ['referencia_donacion' => 'FGHJKL'],
      array(
      'tipo_ofrenda_id' => '2',
      'valor' => '60000.50',
      'observacion' => '',
      'ingresada_por' => '1',
      'user_id' => '9',
      'fecha' => '2025/04/03',
    ));

    // Ofrenda::create(array(
    //   'tipo_ofrenda_id' => '1',
    //   'valor' => '100000',
    //   'observacion' => '',
    //   'ingresada_por' => '0',
    //   'fecha' => '2025/04/03',
    //   'referencia_donacion' => 'FGHJKL'
    // ));

    // Ofrenda::create(array(
    //   'tipo_ofrenda_id' => '2',
    //   'valor' => '20000',
    //   'observacion' => '',
    //   'ingresada_por' => '1',
    //   'user_id' => '3',
    //   'fecha' => '2025/04/04',
    //   'referencia_donacion' => 'UIOPAS'
    // ));

    // Ofrenda::create(array(
    //   'tipo_ofrenda_id' => '2',
    //   'valor' => '30000',
    //   'observacion' => '',
    //   'ingresada_por' => '1',
    //   'user_id' => '4',
    //   'fecha' => '2025/04/05',
    //   'referencia_donacion' => 'QWERTY'
    // ));

    // Ofrenda::create(array(
    //   'tipo_ofrenda_id' => '5',
    //   'valor' => '10000',
    //   'observacion' => '',
    //   'ingresada_por' => '1',
    //   'fecha' => '2025/04/05',
    //   'referencia_donacion' => 'QWERTY'
    // ));
  }
}
