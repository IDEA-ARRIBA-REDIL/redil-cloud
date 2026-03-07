<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TipoUsuario;

class TipoUsuarioSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    TipoUsuario::firstOrCreate(
      ['nombre' => 'Pastor'],
      [
      'nombre_plural' => 'Pastores',
      'color' => '#6b2682',
      'icono' => 'ti ti-book',
      'imagen' => 'icono_indicador.png',
      'id_rol_dependiente' => 2,
      'puntaje' => 4
    ]);

    TipoUsuario::firstOrCreate(
      ['nombre' => 'Lider'],
      [
      'nombre_plural' => 'Lideres',
      'color' => '#a251bd',
      'icono' => 'ti ti-star',
      'imagen' => 'icono_indicador.png',
      'id_rol_dependiente' => 3,
      'puntaje' => 3
    ]);

    TipoUsuario::firstOrCreate(
      ['nombre' => 'Hermano menor'],
      [
      'nombre_plural' => 'Hermano menor',
      'color' => '#dd4b39',
      'icono' => 'ti ti-mood-heart',
      'imagen' => 'icono_indicador.png',
      'id_rol_dependiente' => 4,
      'puntaje' => 2,
      'habilitado_para_consolidacion' => true,
    ]);

    TipoUsuario::firstOrCreate(
      ['nombre' => 'Nuevo'],
      [
      'nombre_plural' => 'Nuevos',
      'color' => '#00c0ef',
      'icono' => 'ti ti-mood-smile',
      'imagen' => 'icono_indicador.png',
      'id_rol_dependiente' => 5,
      'default' => TRUE,
      'puntaje' => 1,
      'habilitado_para_consolidacion' => true,
    ]);

    TipoUsuario::firstOrCreate(
      ['nombre' => 'Empleado'],
      [
      'nombre_plural' => 'Empleados',
      'color' => '#055498',
      'icono' => 'ti ti-building-skyscraper',
      'imagen' => 'icono_indicador.png',
      'id_rol_dependiente' => 6,
      'puntaje' => 0
    ]);

    TipoUsuario::firstOrCreate(
      ['nombre' => 'Desarrollador'],
      [
      'nombre_plural' => 'Desarrolladores',
      'color' => '#055498',
      'icono' => 'ti ti-building-skyscraper',
      'imagen' => 'icono_indicador.png',
      'id_rol_dependiente' => 7,
      'visible' => 0,
      'puntaje' => 0
    ]);
  }
}
