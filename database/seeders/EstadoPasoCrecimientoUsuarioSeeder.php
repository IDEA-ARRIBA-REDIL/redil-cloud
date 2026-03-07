<?php

namespace Database\Seeders;

use App\Models\EstadoPasoCrecimientoUsuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstadoPasoCrecimientoUsuarioSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    EstadoPasoCrecimientoUsuario::firstOrCreate([
      'nombre' => 'No realizado',
      'color' => 'danger',
      'puntaje' => 0,
      'default' => true
    ]);

    EstadoPasoCrecimientoUsuario::firstOrCreate([
      'nombre' => 'En proceso',
      'color' => 'warning',
      'puntaje' => 1
    ]);

    EstadoPasoCrecimientoUsuario::firstOrCreate([
      'nombre' => 'Finalizado',
      'color' => 'success',
      'puntaje' => 2
    ]);
  }
}
