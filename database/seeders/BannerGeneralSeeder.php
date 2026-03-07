<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BannerGeneral;
use Carbon\Carbon;

class BannerGeneralSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Banner 1: Activo y Visible
    BannerGeneral::firstOrCreate([
      'nombre'       => 'Bienvenida al Dashboard',
      'imagen'       => 'banner_1767124764.jpeg', // Imagen de prueba online
      'fecha_inicio' => Carbon::now()->subDays(5)->format('Y-m-d'),
      'fecha_fin'    => Carbon::now()->addDays(30)->format('Y-m-d'),
      'visible'      => true,
    ]);

    // Banner 2: Activo y Visible (Campaña Jovenes)
    BannerGeneral::firstOrCreate([
      'nombre'       => 'Campaña Jóvenes 2024',
      'imagen'       => 'banner_1767127511.jpeg',
      'fecha_inicio' => Carbon::now()->format('Y-m-d'),
      'fecha_fin'    => Carbon::now()->addDays(15)->format('Y-m-d'),
      'visible'      => true,
    ]);

    // Banner 3: INVISIBLE (Para probar que tu filtro funciona)
    BannerGeneral::firstOrCreate([
      'nombre'       => 'Banner Oculto Admin',
      'imagen'       => 'banner_1767124726.jpeg',
      'fecha_inicio' => Carbon::now()->format('Y-m-d'),
      'fecha_fin'    => Carbon::now()->addDays(10)->format('Y-m-d'),
      'visible'      => false, // Este no debería salir en tu vista
    ]);

    // Banner 4: Visible
    BannerGeneral::firstOrCreate([
      'nombre'       => 'Retiro Espiritual',
      'imagen'       => 'banner_1767026177.jpeg',
      'fecha_inicio' => Carbon::now()->addDays(1)->format('Y-m-d'),
      'fecha_fin'    => Carbon::now()->addMonths(1)->format('Y-m-d'),
      'visible'      => true,
    ]);
  }
}
