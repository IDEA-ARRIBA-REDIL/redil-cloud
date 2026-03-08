<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\HorarioHabitual;
use App\Models\Informe;
use App\Models\ReporteReunion;
use App\Models\SemanaDeshabilitadas;
use App\Models\TipoEgreso;
use App\Models\TipoInasistencia;
use App\Models\TipoInforme;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // Solo ejecutamos los seeders base si estamos en entorno local.
    // En el servidor del cliente (producción), solo se ejecutarán los seeders incrementales.
    if (app()->environment('local')) {
      $this->baseSeeders();
    }

    $this->incrementalSeeders();
  }

  /**
   * Seeders base iniciales (Estado actual de la DB).
   */
  protected function baseSeeders(): void
  {
      // TODO: Add global seeders (like TenantSeeder) here if needed.
  }

  /**
   * Seeders incrementales (Nuevos a partir de hoy). 2026
   * Agrega aquí los nuevos seeders para que se ejecuten en todos los entornos. nuevos no alterar los de arriba
   */
  protected function incrementalSeeders(): void
  {
      // TODO: Add new global incrementals here.
  }
}
