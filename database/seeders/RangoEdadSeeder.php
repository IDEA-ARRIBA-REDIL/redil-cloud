<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\RangoEdad;

class RangoEdadSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    RangoEdad::firstOrCreate(
      ['nombre' => 'Bebes'],
      [
      'descripcion' => 'Bebes 0 - 2 años',
      'edad_minima' => 0,
      'edad_maxima' => 2,
      'configuracion_id' => 1,
    ]);

    RangoEdad::firstOrCreate(
      ['nombre' => 'Niños'],
      [
      'descripcion' => 'Niños (3 - 13 años)',
      'edad_minima' => 3,
      'edad_maxima' => 12,
      'configuracion_id' => 1,
    ]);

    RangoEdad::firstOrCreate(
      ['nombre' => 'Teens'],
      [
      'descripcion' => 'Teens (14 - 17 años)',
      'edad_minima' => 13,
      'edad_maxima' => 17,
      'configuracion_id' => 1,
    ]);

    RangoEdad::firstOrCreate(
      ['nombre' => 'Young'],
      [
      'descripcion' => 'Young (18 - 24 años)',
      'edad_minima' => 18,
      'edad_maxima' => 24,
      'configuracion_id' => 1,
    ]);

    RangoEdad::firstOrCreate(
      ['nombre' => 'Pro'],
      [
      'descripcion' => 'Pro (25 - 30 años)',
      'edad_minima' => 25,
      'edad_maxima' => 30,
      'configuracion_id' => 1,
    ]);

    RangoEdad::firstOrCreate(
      ['nombre' => 'Adultos'],
      [
      'descripcion' => 'Adultos (31 años en adelante)',
      'edad_minima' => 31,
      'edad_maxima' => 110,
      'configuracion_id' => 1,
    ]);
  }
}
