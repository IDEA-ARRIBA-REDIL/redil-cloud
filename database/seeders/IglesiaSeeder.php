<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Iglesia;

class IglesiaSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Iglesia::firstOrCreate([
      'nombre' => 'Iglesia Manatial de Vida Eterna',
      'logo' => 'logo manantial.png',
      'municipio_id' => 1089,
      'pais_id' => 45,
      'latitud' => '4.0747',
      'longitud' => '-76.2016',
      'website' => 'soymanantial.com',
      'identificacion' => '800.116.748-1',
      'direccion' => 'Av. Calle 17 # 80 A - 50',
      'telefono1' => '(305) 7341942',
      'email_soporte' => 'eventos@manantial.co',
      'url_subdominio' => 'redil.ubicalo.com'

    ]);
  }
}
